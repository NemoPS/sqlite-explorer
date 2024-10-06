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