<?php

// Set the content type to HTML
header('Content-Type: text/html; charset=utf-8');

session_start();

$db = null;
$tables = [];
$structure = [];
$data = [];
$error = '';
$databasePath = '';
$currentDatabase = '';
$selectedTable = '';
$activeTab = $_GET['activeTab'] ?? 'data';

// Define pagination variables
$tablesPerPage = 20;
$rowsPerPage = 100;
$currentTablePage = isset($_GET['table_page']) ? max(1, intval($_GET['table_page'])) : 1;
$currentRowPage = isset($_GET['row_page']) ? max(1, intval($_GET['row_page'])) : 1;

// Function to get a persistent temporary file path
function getTempFilePath($originalName)
{
    $tempDir = sys_get_temp_dir();
    return $tempDir . DIRECTORY_SEPARATOR . 'sqlite_browser_' . md5($originalName) . '.sqlite';
}

// Add these functions near the top of the file, after the existing function definitions

function getDirectoryContents($path)
{
    $contents = [];
    $items = scandir($path);

    foreach ($items as $item) {
        if ($item != "." && $item != ".." && !(is_dir($path . DIRECTORY_SEPARATOR . $item) && $item[0] === '.')) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            $isDir = is_dir($fullPath);
            $isSqlite = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'sqlite';

            if ($isDir || $isSqlite) {
                $truncatedName = strlen($item) > 30 ? substr($item, 0, 27) . '...' : $item;
                $contents[] = [
                    'name' => $truncatedName,
                    'fullName' => $item,
                    'path' => $fullPath,
                    'type' => $isDir ? 'directory' : 'sqlite',
                ];
            }
        }
    }

    usort($contents, function ($a, $b) {
        if ($a['type'] == $b['type']) {
            return strcasecmp($a['fullName'], $b['fullName']);
        }
        return ($a['type'] == 'directory') ? -1 : 1;
    });

    return $contents;
}

function getParentDirectory($path)
{
    return dirname($path);
}

// Add this near the top of the file, where other variables are initialized
$currentPath = isset($_GET['path']) ? $_GET['path'] : getcwd();
$directoryContents = getDirectoryContents($currentPath);
$parentDirectory = getParentDirectory($currentPath);

// Add this to handle AJAX requests for directory contents
if (isset($_GET['action']) && $_GET['action'] === 'get_directory_contents') {
    header('Content-Type: application/json');
    echo json_encode([
        'contents' => getDirectoryContents($_GET['path']),
        'parent' => getParentDirectory($_GET['path'])
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['database_path'])) {
        $databasePath = $_POST['database_path'];
        if (file_exists($databasePath) && is_readable($databasePath)) {
            $_SESSION['current_database'] = $databasePath;
            $_SESSION['original_filename'] = basename($databasePath);
        } else {
            $error = "Error: The specified database file does not exist or is not readable.";
        }
    }
}

if (isset($_SESSION['current_database'])) {
    $databasePath = $_SESSION['current_database'];
    $currentDatabase = $_SESSION['original_filename'] ?? basename($databasePath);
    $currentPath = dirname($databasePath); // Add this line to set the current path
}

if ($databasePath) {
    try {
        $db = new PDO("sqlite:$databasePath");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $totalTables = getTotalTables($db);
        $tables = getTables($db, $currentTablePage, $tablesPerPage);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle GET request for table selection
if (isset($_GET['table'])) {
    $selectedTable = $_GET['table'];
    if ($db) {
        if (tableExists($db, $selectedTable)) {
            $structure = getTableStructure($db, $selectedTable);
            $totalRows = getTotalRows($db, $selectedTable);

            // Calculate total pages
            $totalRowPages = ceil($totalRows / $rowsPerPage);

            // Ensure current page is within bounds
            $currentRowPage = max(1, min($currentRowPage, $totalRowPages));

            $data = getTableData($db, $selectedTable, $currentRowPage, $rowsPerPage);
        } else {
            $error = "Error: The table '$selectedTable' does not exist in the database.";
        }
    } else {
        $error = "Error: No database connection available.";
    }
}

// Add this near the top of the file, where other actions are handled
if (isset($_GET['action']) && $_GET['action'] === 'insert' && isset($_GET['table'])) {
    $selectedTable = $_GET['table'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');

            $jsonData = file_get_contents('php://input');
            $formData = json_decode($jsonData, true);

            if ($formData === null) {
                echo json_encode(['error' => true, 'message' => 'Invalid JSON data']);
                exit;
            }

            try {
                $structure = getTableStructure($db, $selectedTable);
                $columnTypes = [];
                $autoIncrementColumn = null;

                foreach ($structure as $column) {
                    $columnTypes[$column['name']] = $column['type'];
                    if ($column['pk'] == 1 && $column['type'] == 'INTEGER') {
                        $autoIncrementColumn = $column['name'];
                    }
                }

                // Always remove the 'id' column from formData
                unset($formData['id']);

                $columns = implode(', ', array_keys($formData));
                $placeholders = implode(', ', array_fill(0, count($formData), '?'));
                $sql = "INSERT INTO " . SQLite3::escapeString($selectedTable) . " ($columns) VALUES ($placeholders)";

                error_log("SQL: $sql");
                error_log("Form Data: " . print_r($formData, true));

                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Failed to prepare statement: " . print_r($db->errorInfo(), true));
                }

                $i = 1;
                foreach ($formData as $column => $value) {
                    $type = PDO::PARAM_STR;
                    if (isset($columnTypes[$column])) {
                        switch (strtolower($columnTypes[$column])) {
                            case 'integer':
                                $type = PDO::PARAM_INT;
                                $value = $value === '' ? null : (int)$value;
                                break;
                            case 'real':
                                $type = PDO::PARAM_STR;
                                $value = $value === '' ? null : (float)$value;
                                break;
                            case 'boolean':
                                $type = PDO::PARAM_BOOL;
                                $value = $value === '' ? null : (bool)$value;
                                break;
                            case 'blob':
                                $type = PDO::PARAM_LOB;
                                break;
                        }
                    }
                    $stmt->bindValue($i, $value, $type);
                    error_log("Binding: Column=$column, Value=$value, Type=$type");
                    $i++;
                }

                $result = $stmt->execute();
                if ($result === false) {
                    throw new Exception("Failed to execute statement: " . print_r($stmt->errorInfo(), true));
                }

                $lastInsertId = $db->lastInsertId();
                error_log("Last Insert ID: $lastInsertId");

                echo json_encode(['error' => false, 'message' => 'Data inserted successfully', 'lastInsertId' => $lastInsertId]);
            } catch (Exception $e) {
                error_log("Exception: " . $e->getMessage());
                echo json_encode(['error' => true, 'message' => 'Error inserting data: ' . $e->getMessage()]);
            }
            exit;
        }
    }
}

// Add this near the top of the file, where other actions are handled
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['table'])) {
    $selectedTable = $_GET['table'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');

            // Get JSON data from the request body
            $jsonData = file_get_contents('php://input');
            $rowData = json_decode($jsonData, true);

            if (!empty($rowData)) {
                try {
                    $whereConditions = [];
                    $whereValues = [];
                    foreach ($rowData as $column => $value) {
                        $whereConditions[] = "\"$column\" = ?";
                        $whereValues[] = $value;
                    }
                    $whereClause = implode(' AND ', $whereConditions);

                    $sql = "DELETE FROM \"$selectedTable\" WHERE $whereClause";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($whereValues);

                    if ($stmt->rowCount() > 0) {
                        echo json_encode(['success' => true, 'message' => 'Row deleted successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'No rows were deleted.']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Error deleting row: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No valid data provided.']);
            }
            exit;
        }
    }
}

// Add this near the top of the file, where other actions are handled
if (isset($_GET['action']) && $_GET['action'] === 'bulk_delete' && isset($_GET['table'])) {
    $selectedTable = $_GET['table'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');

            // Get JSON data from the request body
            $jsonData = file_get_contents('php://input');
            $rowsToDelete = json_decode($jsonData, true);

            if (!empty($rowsToDelete)) {
                try {
                    $db->beginTransaction();
                    $deletedCount = 0;

                    foreach ($rowsToDelete as $row) {
                        $whereConditions = [];
                        $whereValues = [];
                        foreach ($row as $column => $value) {
                            $whereConditions[] = "\"$column\" = ?";
                            $whereValues[] = $value;
                        }
                        $whereClause = implode(' AND ', $whereConditions);

                        $sql = "DELETE FROM \"$selectedTable\" WHERE $whereClause";
                        $stmt = $db->prepare($sql);
                        $stmt->execute($whereValues);
                        $deletedCount += $stmt->rowCount();
                    }

                    $db->commit();
                    echo json_encode(['success' => true, 'message' => "$deletedCount row(s) deleted successfully."]);
                } catch (PDOException $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error deleting rows: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No valid data provided.']);
            }
            exit;
        }
    }
}

// Include the HTML template
include 'template.php';

// Function to get all tables in the database with pagination
function getTables(PDO $db, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get total number of tables
function getTotalTables(PDO $db): int
{
    $stmt = $db->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    return $stmt->fetchColumn();
}

// Function to check if a table exists
function tableExists(PDO $db, string $table): bool
{
    $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :table");
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetch();
}

// Function to get table structure
function getTableStructure(PDO $db, string $table): array
{
    $escapedTable = SQLite3::escapeString($table);
    $stmt = $db->query("PRAGMA table_info(\"$escapedTable\")");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get table data with pagination and sorting
function getTableData(PDO $db, string $table, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;
    $escapedTable = SQLite3::escapeString($table);

    // Get table structure to check for ID or timestamp columns
    $structure = getTableStructure($db, $table);
    $idColumn = null;
    $timestampColumn = null;

    foreach ($structure as $column) {
        $columnName = strtolower($column['name']);
        if ($columnName === 'id' || $columnName === 'rowid') {
            $idColumn = $column['name'];
            break;
        } elseif (strpos($columnName, 'time') !== false || strpos($columnName, 'date') !== false) {
            $timestampColumn = $column['name'];
        }
    }

    // Prepare the SQL query with appropriate sorting
    if ($idColumn) {
        $sql = "SELECT * FROM \"$escapedTable\" ORDER BY \"$idColumn\" DESC LIMIT :limit OFFSET :offset";
    } elseif ($timestampColumn) {
        $sql = "SELECT * FROM \"$escapedTable\" ORDER BY \"$timestampColumn\" DESC LIMIT :limit OFFSET :offset";
    } else {
        $sql = "SELECT * FROM \"$escapedTable\" LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get total number of rows in a table
function getTotalRows(PDO $db, string $table): int
{
    $escapedTable = SQLite3::escapeString($table);
    $stmt = $db->query("SELECT COUNT(*) FROM \"$escapedTable\"");
    return $stmt->fetchColumn();
}
