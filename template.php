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
    <?php include 'components/header.php'; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="flex-1 flex overflow-hidden p-4">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 overflow-hidden bg-white shadow-md ml-4 p-4">
            <?php if ($selectedTable): ?>
                <h2 class="text-2xl font-semibold mb-4">Table: <?= htmlspecialchars($selectedTable) ?></h2>

                <div class="mb-4">
                    <button @click="activeTab = 'data'" :class="{ 'bg-indigo-500 text-white': activeTab === 'data', 'bg-gray-200': activeTab !== 'data' }" class="px-4 py-2 rounded-l-md">Data</button>
                    <button @click="activeTab = 'structure'" :class="{ 'bg-indigo-500 text-white': activeTab === 'structure', 'bg-gray-200': activeTab !== 'structure' }" class="px-4 py-2">Structure</button>
                    <button @click="activeTab = 'insert'" :class="{ 'bg-indigo-500 text-white': activeTab === 'insert', 'bg-gray-200': activeTab !== 'insert' }" class="px-4 py-2 rounded-r-md">Insert</button>
                </div>

                <div x-show="activeTab === 'data'">
                    <?php include 'components/data_table.php'; ?>
                </div>

                <div x-show="activeTab === 'structure'">
                    <?php include 'components/structure_table.php'; ?>
                </div>

                <div x-show="activeTab === 'insert'">
                    <?php include 'components/insert_form.php'; ?>
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