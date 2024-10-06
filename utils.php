<?php

declare(strict_types=1);

function getTableColumns(PDO $db, string $tableName): array
{
    $columns = [];
    $stmt = $db->prepare("PRAGMA table_info(:tableName)");
    $stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = [
            'name' => $row['name'],
            'type' => $row['type'],
            'pk' => $row['pk'] == 1
        ];
    }

    return $columns;
}
