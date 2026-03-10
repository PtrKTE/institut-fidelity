<?php

/**
 * Laravel front controller for subdirectory deployment under MAMP Pro.
 */

chdir(__DIR__ . '/public');

$prefix = '/fidelity-laravel';

// Strip subdirectory prefix and /index.php from REQUEST_URI
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = preg_replace('#^' . preg_quote($prefix) . '(/index\.php)?#', '', $uri);
    $_SERVER['REQUEST_URI'] = $uri ?: '/';
}

// Tell PHP/Laravel we're in a subdirectory
$_SERVER['SCRIPT_NAME'] = $prefix . '/index.php';
$_SERVER['PHP_SELF'] = $prefix . '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';

require __DIR__ . '/public/index.php';
