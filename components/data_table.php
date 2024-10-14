<?php
function truncateText($text, $length = 50)
{
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<div x-data="dataTable()" class="flex flex-col h-full">
    <div class="mb-4 flex justify-between items-center">
        <div class="flex space-x-2">
            <button @click="bulkDelete" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">Delete Selected</button>
            <button class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">Export Selected</button>
        </div>
        <div x-text="`${selectedRows.length} row(s) selected`" class="text-gray-600"></div>
    </div>
    <?php if ($data): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" @click="toggleAll" x-bind:checked="allSelected">
                        </th>
                        <?php foreach (array_keys($data[0]) as $column): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= htmlspecialchars($column) ?></th>
                        <?php endforeach; ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data as $index => $row): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" @click="toggleRow(<?= htmlspecialchars(json_encode($row)) ?>)" :checked="isSelected(<?= htmlspecialchars(json_encode($row)) ?>)">
                            </td>
                            <?php foreach ($row as $value): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" title="<?= htmlspecialchars((string)$value) ?>">
                                    <?= htmlspecialchars(truncateText((string)$value)) ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900 mr-2">
                                    <img src="https://unpkg.com/lucide-static@latest/icons/edit.svg" class="w-5 h-5" alt="Edit icon" />
                                </button>
                                <button @click="deleteRow(<?= htmlspecialchars(json_encode($row)) ?>)" class="text-red-600 hover:text-red-900">
                                    <img src="https://unpkg.com/lucide-static@latest/icons/trash-2.svg" class="w-5 h-5" alt="Delete icon" />
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No data found in this table.</p>
    <?php endif; ?>
</div>

<script>
    function dataTable() {
        return {
            selectedRows: [],
            allSelected: false,
            toggleAll() {
                this.allSelected = !this.allSelected;
                this.selectedRows = this.allSelected ? <?= json_encode($data) ?> : [];
            },
            isSelected(row) {
                return this.selectedRows.some(selectedRow => JSON.stringify(selectedRow) === JSON.stringify(row));
            },
            toggleRow(row) {
                const index = this.selectedRows.findIndex(selectedRow => JSON.stringify(selectedRow) === JSON.stringify(row));
                if (index === -1) {
                    this.selectedRows.push(row);
                } else {
                    this.selectedRows.splice(index, 1);
                }
                this.allSelected = this.selectedRows.length === <?= count($data) ?>;
            },
            bulkDelete() {
                if (confirm(`Are you sure you want to delete ${this.selectedRows.length} row(s)? This action cannot be undone.`)) {
                    fetch('?action=bulk_delete&table=<?= urlencode($selectedTable) ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.selectedRows)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Error deleting rows: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An unexpected error occurred.');
                        });
                }
            },
            deleteRow(row) {
                if (confirm('Are you sure you want to delete this row? This action cannot be undone.')) {
                    fetch('?action=delete&table=<?= urlencode($selectedTable) ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(row)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Error deleting row: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An unexpected error occurred.');
                        });
                }
            }
        }
    }
</script>