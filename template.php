<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLite Browser</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full flex flex-col" x-data="{ headerExpanded: false, activeTab: 'data' }">
    <!-- Collapsible Header -->
    <header class="bg-white shadow-sm" x-data="{ headerExpanded: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">
                <h1 class="text-xl font-semibold text-gray-900">SQLite Browser</h1>
                <button @click="headerExpanded = !headerExpanded" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" x-show="!headerExpanded" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" x-show="headerExpanded" />
                    </svg>
                </button>
            </div>
        </div>
        <!-- Expandable content -->
        <div x-show="headerExpanded" x-collapse>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <h2 class="text-lg font-semibold mb-2">Open SQLite Database</h2>
                <form action="" method="post" enctype="multipart/form-data" class="mb-2">
                    <div class="flex items-center mb-2">
                        <input type="text" id="file_name" readonly placeholder="Select a SQLite database file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <input type="file" id="file_selector" name="database_file" accept=".sqlite,.db" class="hidden">
                        <button type="button" onclick="document.getElementById('file_selector').click();" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Browse
                        </button>
                    </div>
                    <input type="submit" value="Open Database" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                </form>
                <?php if ($currentDatabase): ?>
                    <div class="text-sm text-indigo-700">
                        Current Database: <?= htmlspecialchars($currentDatabase) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="flex-1 flex overflow-hidden p-4">
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

        <main class="flex-1 overflow-hidden bg-white shadow-md ml-4 p-4">
            <?php if ($selectedTable): ?>
                <h2 class="text-2xl font-semibold mb-4">Table: <?= htmlspecialchars($selectedTable) ?></h2>

                <div class="mb-4">
                    <button @click="activeTab = 'data'" :class="{ 'bg-indigo-500 text-white': activeTab === 'data', 'bg-gray-200': activeTab !== 'data' }" class="px-4 py-2 rounded-l-md">Data</button>
                    <button @click="activeTab = 'structure'" :class="{ 'bg-indigo-500 text-white': activeTab === 'structure', 'bg-gray-200': activeTab !== 'structure' }" class="px-4 py-2 rounded-r-md">Structure</button>
                </div>

                <div x-show="activeTab === 'data'">
                    <?php if ($data): ?>
                        <div class="h-[calc(100vh-250px)] overflow-hidden" id="tableWrapper">
                            <div class="overflow-auto h-full" id="tableContainer">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                        <tr>
                                            <?php foreach (array_keys($data[0]) as $column): ?>
                                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= htmlspecialchars($column) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($data as $index => $row): ?>
                                            <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                                                <?php foreach ($row as $value): ?>
                                                    <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars((string)$value) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            </div>

                        </div>
                        <!-- Pagination -->
                        <?php if ($totalRowPages > 1): ?>
                            <div class="flex space-x-1 mt-2">
                                <a href="?table=<?= urlencode($selectedTable) ?>&row_page=1" class="px-2 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">First</a>

                                <?php
                                $range = 2; // Number of pages to show before and after the current page
                                $start = max(1, $currentRowPage - $range);
                                $end = min($totalRowPages, $currentRowPage + $range);

                                if ($start > 1) {
                                    echo '<span class="px-2 py-1 text-xs">...</span>';
                                }

                                for ($i = $start; $i <= $end; $i++) {
                                    $class = ($i == $currentRowPage) ? 'bg-indigo-500 text-white' : 'bg-gray-200 hover:bg-gray-300';
                                    echo "<a href='?table=" . urlencode($selectedTable) . "&row_page=$i' class='px-2 py-1 text-xs $class rounded'>$i</a>";
                                }

                                if ($end < $totalRowPages) {
                                    echo '<span class="px-2 py-1 text-xs">...</span>';
                                }
                                ?>

                                <a href="?table=<?= urlencode($selectedTable) ?>&row_page=<?= $totalRowPages ?>" class="px-2 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">Last</a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>No data found in this table.</p>
                    <?php endif; ?>
                </div>

                <div x-show="activeTab === 'structure'">
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
                </div>
            <?php else: ?>
                <p class="text-lg">Select a table from the list on the left to view its structure and data.</p>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.getElementById('file_selector').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                document.getElementById('file_name').value = file.name;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var tableWrapper = document.getElementById('tableWrapper');
            var tableContainer = document.getElementById('tableContainer');
            var scrollContainer = document.getElementById('scrollContainer');
            var scrollContent = document.getElementById('scrollContent');
            var table = tableContainer ? tableContainer.querySelector('table') : null;

            function updateScrollContentWidth() {
                if (table && scrollContent && tableContainer) {
                    var tableWidth = table.offsetWidth;
                    var containerWidth = tableContainer.offsetWidth;
                    scrollContent.style.width = Math.max(tableWidth, containerWidth) + 'px';
                }
            }

            function syncScroll() {
                if (this === tableContainer) {
                    scrollContainer.scrollLeft = tableContainer.scrollLeft;
                } else {
                    tableContainer.scrollLeft = scrollContainer.scrollLeft;
                }
            }

            if (tableContainer && scrollContainer) {
                updateScrollContentWidth();
                window.addEventListener('resize', updateScrollContentWidth);

                tableContainer.addEventListener('scroll', syncScroll);
                scrollContainer.addEventListener('scroll', syncScroll);
            }
        });
    </script>
</body>

</html>