<?php
// ... (existing code)

if ($selectedTable) {
    // Get total number of rows
    $totalRowsQuery = $db->query("SELECT COUNT(*) as count FROM " . SQLite3::escapeString($selectedTable));
    $totalRows = $totalRowsQuery->fetchArray(SQLITE3_ASSOC)['count'];

    // Calculate total pages
    $rowsPerPage = 100; // Or whatever number you're using
    $totalRowPages = ceil($totalRows / $rowsPerPage);

    // Get current page
    $currentRowPage = isset($_GET['row_page']) ? (int)$_GET['row_page'] : 1;
    $currentRowPage = max(1, min($currentRowPage, $totalRowPages));

    // Calculate offset
    $offset = ($currentRowPage - 1) * $rowsPerPage;

    // Fetch data for current page
    $query = $db->query("SELECT * FROM " . SQLite3::escapeString($selectedTable) . " LIMIT $rowsPerPage OFFSET $offset");
    $data = [];
    while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }

    // ... (rest of your existing code)
}

// ... (rest of your existing code)

// Include the template
include 'template.php';
