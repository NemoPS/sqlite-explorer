<?php

if ($argc < 2 || $argv[1] === 'serve') {
    startServer();
} else {
    echo "Usage: ./index serve\n";
    exit(1);
}

function startServer(): void
{
    $host = '127.0.0.1';
    $port = 8000;
    echo "Starting server on http://$host:$port\n";
    echo "Press Ctrl-C to quit.\n";

    // Use the current directory as the document root
    $documentRoot = __DIR__;

    // Start the built-in PHP server with the router script
    $routerScript = __DIR__ . '/router.php';
    passthru("php -S $host:$port -t $documentRoot $routerScript");
}
