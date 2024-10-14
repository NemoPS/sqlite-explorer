<aside class="w-64 bg-gray-100 overflow-y-auto flex flex-col border-r border-gray-200">
    <div class="p-4 border-b border-gray-200">
        <h1 class="text-xl font-bold flex items-center">
            <i class="icon-database w-6 h-6 mr-2"></i>
            Database Tables
        </h1>
        <?php include 'components/header.php'; ?>
    </div>
    <ul class="flex-1">
        <?php foreach ($tables as $table): ?>
            <li>
                <a href="?table=<?= urlencode($table) ?>" class="block px-4 py-2 hover:bg-gray-200 <?= $selectedTable === $table ? 'bg-gray-200' : '' ?>">
                    <i class="icon-table w-4 h-4 inline-block mr-2"></i>
                    <?= htmlspecialchars($table) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>