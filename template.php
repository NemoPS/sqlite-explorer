<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLite Browser</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full flex flex-col" x-data="mainApp">
    <div class="flex-1 flex overflow-hidden">
        <?php include 'components/sidebar.php'; ?>

        <main class="flex-1 overflow-hidden bg-white flex flex-col h-full">
            <?php if ($selectedTable): ?>
                <div class="bg-gray-100 p-4 flex justify-between items-center border-b border-gray-200">
                    <h2 class="text-2xl font-bold"><?= htmlspecialchars($selectedTable) ?></h2>
                    <div class="flex space-x-2">
                        <button @click="showInsertDrawer = true" class="bg-black hover:bg-gray-800 text-white px-3 py-1 rounded text-sm flex items-center">
                            <i class="icon-plus w-4 h-4 mr-1"></i>
                            Add Row
                        </button>
                        <button @click="$dispatch('refresh-data')" class="bg-white hover:bg-gray-100 text-black px-3 py-1 rounded border border-gray-300 text-sm flex items-center">
                            <i class="icon-refresh-cw w-4 h-4 mr-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-auto p-4">
                    <div x-show="activeView === 'data'">
                        <?php include 'components/data_table.php'; ?>
                    </div>
                    <div x-show="activeView === 'structure'">
                        <?php include 'components/structure_table.php'; ?>
                    </div>
                </div>

                <div class="bg-gray-100 border-t border-gray-200 p-4 flex justify-between items-center">
                    <div>
                        <?php include 'components/pagination.php'; ?>
                    </div>
                    <div class="flex space-x-2">
                        <button @click="activeView = 'data'" :class="{ 'bg-white text-black border-gray-300 border': activeView === 'data', 'bg-gray-200 text-gray-600': activeView !== 'data' }" class="px-3 py-1 rounded text-sm">Data</button>
                        <button @click="activeView = 'structure'" :class="{ 'bg-white text-black border-gray-300 border': activeView === 'structure', 'bg-gray-200 text-gray-600': activeView !== 'structure' }" class="px-3 py-1 rounded text-sm">Structure</button>
                    </div>
                </div>

                <!-- Insert Form Drawer -->
                <div x-show="showInsertDrawer"
                    class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl overflow-y-auto"
                    @click.away="showInsertDrawer = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-4">Insert New Record</h2>
                        <?php include 'components/insert_form.php'; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="p-4 text-gray-500">Select a table from the sidebar to view its contents.</p>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mainApp', () => ({
                activeView: 'data',
                showInsertDrawer: false,
                init() {
                    this.$watch('showInsertDrawer', (value) => {
                        if (!value) {
                            // Reset the form when the drawer is closed
                            this.$refs.insertForm.reset();
                        }
                    });
                }
            }));
        });

        // Add an event listener for refreshing data
        window.addEventListener('refresh-data', () => {
            // Make an AJAX request to fetch the updated data
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    // Parse the HTML and update the table content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.data-table');
                    const currentTable = document.querySelector('.data-table');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    // Fallback to page reload if AJAX refresh fails
                    location.reload();
                });
        });
    </script>
</body>

</html>