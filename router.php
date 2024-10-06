<?php

$uri = $_SERVER['REQUEST_URI'];

// If the request is for the root or has query parameters, route to web.php
if ($uri === '/' || strpos($uri, '?') !== false) {
    require __DIR__ . '/web.php';
    return;
}

// For all other requests, check if the file exists
$file = __DIR__ . $uri;
if (is_file($file)) {
    return false; // Let the server handle existing files
}

// If no file exists, route to web.php
require __DIR__ . '/web.php';
