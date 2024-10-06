<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLite Browser</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-wrapper {
            overflow-x: auto;
            max-width: 100%;
        }

        .truncate-cell {
            max-width: 200px;
            max-height: 3em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
        }

        .popup {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            max-width: 400px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">SQLite Browser</h1>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl font-semibold mb-4">Open SQLite Database</h2>
            <form action="" method="post" enctype="multipart/form-data" class="mb-4">
                <div class="flex items-center mb-2">
                    <input type="text" id="file_name" readonly placeholder="Select a SQLite database file" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <input type="file" id="file_selector" name="database_file" accept=".sqlite,.db" class="hidden">
                    <button type="button" onclick="document.getElementById('file_selector').click();" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Browse
                    </button>
                </div>
                <input type="submit" value="Open Database" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            </form>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($currentDatabase): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">Current Database: <?= htmlspecialchars($currentDatabase) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($db && $tables): ?>
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-1/4 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 md:mr-4">
                    <h2 class="text-xl font-semibold mb-4">Tables</h2>
                    <ul>
                        <?php foreach ($tables as $table): ?>
                            <li class="mb-2">
                                <a href="?table=<?= urlencode($table) ?>" class="text-blue-500 hover:text-blue-800"><?= htmlspecialchars($table) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                    $totalTablePages = ceil($totalTables / $tablesPerPage);
                    if ($totalTablePages > 1):
                    ?>
                        <div class="mt-4">
                            <?php for ($i = 1; $i <= $totalTablePages; $i++): ?>
                                <a href="?table_page=<?= $i ?>" class="inline-block px-2 py-1 mr-1 mb-1 <?= $i === $currentTablePage ? 'bg-blue-500 text-white' : 'bg-gray-200' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="w-full md:w-3/4 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <?php if ($selectedTable): ?>
                        <h2 class="text-2xl font-semibold mb-4">Table: <?= htmlspecialchars($selectedTable) ?></h2>

                        <!-- Tabs -->
                        <div class="mb-4">
                            <button onclick="showTab('data')" class="px-4 py-2 text-sm font-medium text-center text-gray-500 bg-gray-100 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 focus:outline-none" id="data-tab">Data</button>
                            <button onclick="showTab('structure')" class="px-4 py-2 text-sm font-medium text-center text-gray-500 bg-gray-100 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 focus:outline-none" id="structure-tab">Structure</button>
                        </div>

                        <!-- Data Tab Content -->
                        <div id="data-content" class="tab-content">
                            <h3 class="text-xl font-semibold mb-2">Data</h3>
                            <?php if ($data): ?>
                                <p>Page <?= $currentRowPage ?></p>
                                <div class="table-wrapper">
                                    <table class="table-auto w-full">
                                        <thead>
                                            <tr class="bg-gray-200">
                                                <?php foreach (array_keys($data[0]) as $column): ?>
                                                    <th class="px-4 py-2"><?= htmlspecialchars($column) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td class="border px-4 py-2">
                                                            <div class="truncate-cell" onclick="showPopup(this, '<?= htmlspecialchars(addslashes((string)$value)) ?>')">
                                                                <?= htmlspecialchars((string)$value) ?>
                                                            </div>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                                $totalRowPages = ceil($totalRows / $rowsPerPage);
                                if ($totalRowPages > 1):
                                ?>
                                    <div class="mt-4">
                                        <?php for ($i = 1; $i <= $totalRowPages; $i++): ?>
                                            <a href="?table=<?= urlencode($selectedTable) ?>&row_page=<?= $i ?>" class="inline-block px-2 py-1 mr-1 mb-1 <?= $i === $currentRowPage ? 'bg-blue-500 text-white' : 'bg-gray-200' ?>"><?= $i ?></a>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>No data found in this table.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Structure Tab Content -->
                        <div id="structure-content" class="tab-content" style="display: none;">
                            <h3 class="text-xl font-semibold mb-2">Structure</h3>
                            <div class="table-wrapper">
                                <table class="table-auto w-full mb-4">
                                    <thead>
                                        <tr class="bg-gray-200">
                                            <th class="px-4 py-2">Column</th>
                                            <th class="px-4 py-2">Type</th>
                                            <th class="px-4 py-2">Nullable</th>
                                            <th class="px-4 py-2">Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($structure as $column): ?>
                                            <tr>
                                                <td class="border px-4 py-2"><?= htmlspecialchars($column['name']) ?></td>
                                                <td class="border px-4 py-2"><?= htmlspecialchars($column['type']) ?></td>
                                                <td class="border px-4 py-2"><?= $column['notnull'] ? 'No' : 'Yes' ?></td>
                                                <td class="border px-4 py-2"><?= htmlspecialchars($column['dflt_value'] ?? 'NULL') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-xl">Select a table from the list on the left to view its structure and data.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="popup" class="popup"></div>

    <script>
        document.getElementById('file_selector').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                document.getElementById('file_name').value = file.name;
            }
        });

        function showTab(tabName) {
            var tabs = ['structure', 'data'];
            tabs.forEach(function(tab) {
                var content = document.getElementById(tab + '-content');
                var tabButton = document.getElementById(tab + '-tab');
                if (tab === tabName) {
                    content.style.display = 'block';
                    tabButton.classList.add('text-blue-600', 'border-blue-600');
                    tabButton.classList.remove('text-gray-500', 'border-transparent');
                } else {
                    content.style.display = 'none';
                    tabButton.classList.remove('text-blue-600', 'border-blue-600');
                    tabButton.classList.add('text-gray-500', 'border-transparent');
                }
            });
        }

        function showPopup(element, content) {
            var popup = document.getElementById('popup');
            popup.innerHTML = content;
            popup.style.display = 'block';

            var rect = element.getBoundingClientRect();
            popup.style.left = rect.left + 'px';
            popup.style.top = (rect.bottom + window.scrollY) + 'px';
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(event) {
            var popup = document.getElementById('popup');
            if (!event.target.closest('.truncate-cell') && !event.target.closest('.popup')) {
                popup.style.display = 'none';
            }
        });

        // Show the data tab by default
        showTab('data');
    </script>
</body>

</html>