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