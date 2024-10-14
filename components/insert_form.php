<div x-data="insertForm()">
    <div x-show="message" x-text="message" :class="{'text-green-600': !error, 'text-red-600': error}" class="mb-4 p-2 border rounded" :class="{'bg-green-100 border-green-400': !error, 'bg-red-100 border-red-400': error}"></div>

    <form @submit.prevent="submitForm" class="space-y-4">
        <?php
        foreach ($structure as $column) {
            $columnName = $column['name'];
            $columnType = $column['type'];
            $isPrimaryKey = $column['pk'] == 1;
            $isTextArea = strtoupper($columnType) === 'TEXT';
        ?>
            <div>
                <label for="<?= $columnName ?>" class="block text-sm font-medium text-gray-700 mb-1"><?= htmlspecialchars($columnName) ?> (<?= htmlspecialchars($columnType) ?>)</label>
                <?php if ($isTextArea): ?>
                    <textarea
                        name="<?= $columnName ?>"
                        id="<?= $columnName ?>"
                        x-model="formData.<?= $columnName ?>"
                        rows="4"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400
                        focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                        <?= $isPrimaryKey ? 'bg-gray-100' : '' ?>"
                        <?= $isPrimaryKey ? 'readonly' : '' ?>></textarea>
                <?php else: ?>
                    <input
                        type="text"
                        name="<?= $columnName ?>"
                        id="<?= $columnName ?>"
                        x-model="formData.<?= $columnName ?>"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400
                        focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                        <?= $isPrimaryKey ? 'bg-gray-100' : '' ?>"
                        <?= $isPrimaryKey ? 'readonly' : '' ?>>
                <?php endif; ?>
            </div>
        <?php } ?>
        <div>
            <button
                type="submit"
                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save
            </button>
        </div>
    </form>
</div>

<script>
    function insertForm() {
        return {
            formData: <?= json_encode(array_reduce($structure, function ($carry, $item) {
                            $carry[$item['name']] = '';
                            return $carry;
                        }, [])) ?>,
            message: '',
            error: false,
            submitForm() {
                fetch('?action=insert&table=<?= urlencode($selectedTable) ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.message = data.message;
                        this.error = data.error;
                        if (!data.error) {
                            // Force a full page reload with the data tab selected
                            window.location.href = '?table=<?= urlencode($selectedTable) ?>&activeTab=data&t=' + new Date().getTime();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.message = 'An unexpected error occurred.';
                        this.error = true;
                    });
            }
        }
    }
</script>