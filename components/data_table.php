<?php
function truncateText($text, $length = 50)
{
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<?php if ($data): ?>
    <div class="h-[calc(100vh-250px)] overflow-hidden" id="tableWrapper">
        <div class="overflow-auto h-full" id="tableContainer">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <?php foreach (array_keys($data[0]) as $column): ?>
                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= htmlspecialchars($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data as $index => $row): ?>
                        <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">
                                <a href="#" class="text-blue-600 hover:text-blue-800 mr-2" onclick="editRow(<?= $index ?>); return false;">Edit</a>
                                <a href="#" class="text-red-600 hover:text-red-800" onclick="deleteRow(<?= $index ?>); return false;">Delete</a>
                            </td>
                            <?php foreach ($row as $value): ?>
                                <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 max-w-xs overflow-hidden overflow-ellipsis" title="<?= htmlspecialchars((string)$value) ?>">
                                    <?= htmlspecialchars(truncateText((string)$value)) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'pagination.php'; ?>
<?php else: ?>
    <p>No data found in this table.</p>
<?php endif; ?>

<script>
    function editRow(index) {
        // Implement edit functionality
        console.log('Edit row:', index);
    }

    function deleteRow(index) {
        // Implement delete functionality
        console.log('Delete row:', index);
    }
</script>