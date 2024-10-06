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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['database_file'])) {
        $uploadedFile = $_FILES['database_file'];
        $originalName = $uploadedFile['name'];
        $tempFilePath = getTempFilePath($originalName);

        if (move_uploaded_file($uploadedFile['tmp_name'], $tempFilePath)) {
            $databasePath = $tempFilePath;
            $_SESSION['current_database'] = $databasePath;
            $_SESSION['original_filename'] = $originalName;
        } else {
            $error = "Error: Failed to move the uploaded file.";
        }
    }
}

if (isset($_SESSION['current_database'])) {
    $databasePath = $_SESSION['current_database'];
    $currentDatabase = $_SESSION['original_filename'] ?? basename($databasePath);
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

// Function to get table data with pagination
function getTableData(PDO $db, string $table, int $page, int $perPage): array
{
    $offset = ($page - 1) * $perPage;
    $escapedTable = SQLite3::escapeString($table);
    $stmt = $db->prepare("SELECT * FROM \"$escapedTable\" LIMIT :limit OFFSET :offset");
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
