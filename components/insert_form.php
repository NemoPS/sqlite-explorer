<form action="?action=insert&table=<?= urlencode($selectedTable) ?>" method="post" class="space-y-4">
    <?php
    foreach ($structure as $column) {
        $columnName = $column['name'];
        $columnType = $column['type'];
        $isPrimaryKey = $column['pk'] == 1;
    ?>
        <div>
            <label for="<?= $columnName ?>" class="block text-sm font-medium text-gray-700"><?= htmlspecialchars($columnName) ?> (<?= htmlspecialchars($columnType) ?>)</label>
            <input type="text" name="<?= $columnName ?>" id="<?= $columnName ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" <?= $isPrimaryKey ? 'readonly' : '' ?>>
        </div>
    <?php } ?>
    <div>
        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Insert Data
        </button>
    </div>
</form>