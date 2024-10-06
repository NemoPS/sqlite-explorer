<aside class="w-64 bg-white shadow-md overflow-y-auto p-4">
    <?php if ($databasePath): ?>
        <div class="text-xs">
            Current Database: <br> <span class="text-indigo-700"><?= htmlspecialchars($databasePath) ?></span>
        </div>
    <?php endif; ?>
    <h2 class=" text-lg font-semibold mb-2">Tables</h2>
    <ul>
        <?php foreach ($tables as $table): ?>
            <li class="mb-1">
                <a href="?table=<?= urlencode($table) ?>" class="text-indigo-500 hover:text-blue-800"><?= htmlspecialchars($table) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>