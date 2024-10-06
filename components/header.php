<header class="bg-white shadow-sm" x-data="fileExplorer()">
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
                    <input type="text" name="database_path" x-model="currentPath" placeholder="Select a SQLite database path" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <button type="button" @click="showFileExplorer = true" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Browse
                    </button>
                </div>
                <input type="submit" value="Open Database" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            </form>
        </div>
    </div>

    <!-- File Explorer Modal -->
    <div x-show="showFileExplorer" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white z-50">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">File Explorer</h3>
                <div class="mt-2 px-7 py-3">
                    <div class="text-left mb-4 text-sm text-gray-600 break-all">
                        <span x-text="currentPath"></span>
                    </div>
                    <div class="text-left mb-4">
                        <button @click="navigateDirectory(parentDirectory)" class="text-blue-500 hover:underline">..</button>
                    </div>
                    <ul class="max-h-60 overflow-y-auto">
                        <template x-for="item in contents" :key="item.path">
                            <li class="text-left mb-2">
                                <template x-if="item.type === 'directory'">
                                    <button @click="navigateDirectory(item.path)" class="text-blue-500 hover:underline" :title="item.fullName">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                        <span x-text="item.name"></span>
                                    </button>
                                </template>
                                <template x-if="item.type === 'sqlite'">
                                    <button @click="selectFile(item.path)" class="text-green-500 hover:underline" :title="item.fullName">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                        </svg>
                                        <span x-text="item.name"></span>
                                    </button>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="items-center px-4 py-3">
                    <button @click="showFileExplorer = false" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fileExplorer() {
            return {
                headerExpanded: false,
                showFileExplorer: false,
                currentPath: '<?= htmlspecialchars($currentPath) ?>',
                contents: <?= json_encode($directoryContents) ?>,
                parentDirectory: '<?= htmlspecialchars($parentDirectory) ?>',
                navigateDirectory(path) {
                    fetch(`?action=get_directory_contents&path=${encodeURIComponent(path)}`)
                        .then(response => response.json())
                        .then(data => {
                            this.contents = data.contents;
                            this.parentDirectory = data.parent;
                            this.currentPath = path;
                        });
                },
                selectFile(path) {
                    this.currentPath = path;
                    this.showFileExplorer = false;
                }
            }
        }
    </script>
</header>