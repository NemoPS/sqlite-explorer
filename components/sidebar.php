<aside class="w-64 bg-white shadow-md overflow-y-auto p-4">
    <h2 class="text-lg font-semibold mb-2">Tables</h2>
    <ul>
        <?php foreach ($tables as $table): ?>
            <li class="mb-1">
                <a href="?table=<?= urlencode($table) ?>" class="text-indigo-500 hover:text-blue-800"><?= htmlspecialchars($table) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>