<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nullable</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($structure as $column): ?>
                <tr>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($column['name']) ?></td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($column['type']) ?></td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500"><?= $column['notnull'] ? 'No' : 'Yes' ?></td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($column['dflt_value'] ?? 'NULL') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>