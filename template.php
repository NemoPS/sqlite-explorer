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

<body class="h-full flex flex-col" x-data="{ headerExpanded: false, activeTab: '<?= $_GET['activeTab'] ?? 'data' ?>' }">
    <?php include 'components/header.php'; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 shadow-md" role="alert">
            <div class="flex">
                <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                    </svg></div>
                <div>
                    <p class="font-bold">Error</p>
                    <p class="text-sm"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex-1 flex overflow-hidden p-4">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 overflow-hidden bg-white shadow-md ml-4 p-4 flex flex-col">
            <?php if ($selectedTable): ?>
                <h2 class="text-2xl font-semibold mb-4">Table: <?= htmlspecialchars($selectedTable) ?></h2>

                <div class="mb-4">
                    <button @click="activeTab = 'data'" :class="{ 'bg-blue-500 text-white': activeTab === 'data', 'bg-gray-200 text-gray-700': activeTab !== 'data' }" class="px-4 py-2 rounded-l-md">Data</button>
                    <button @click="activeTab = 'structure'" :class="{ 'bg-blue-500 text-white': activeTab === 'structure', 'bg-gray-200 text-gray-700': activeTab !== 'structure' }" class="px-4 py-2">Structure</button>
                    <button @click="activeTab = 'insert'" :class="{ 'bg-blue-500 text-white': activeTab === 'insert', 'bg-gray-200 text-gray-700': activeTab !== 'insert' }" class="px-4 py-2 rounded-r-md">Insert</button>
                </div>

                <div x-show="activeTab === 'data'" class="flex-1 overflow-auto">
                    <?php include 'components/data_table.php'; ?>
                    <?php include 'components/pagination.php'; ?>
                </div>

                <div x-show="activeTab === 'structure'" class="flex-1 overflow-auto">
                    <?php include 'components/structure_table.php'; ?>
                </div>

                <div x-show="activeTab === 'insert'" class="flex-1 overflow-auto">
                    <?php include 'components/insert_form.php'; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Select a table from the sidebar to view its contents.</p>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>