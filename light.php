#!/usr/bin/env php
<?php

// Find and require the Composer autoloader
$possibleAutoloadPaths = [
    __DIR__ . '/vendor/autoload.php',          // when executed from framework directory
    __DIR__ . '/../autoload.php',              // when executed as a Composer bin script
    __DIR__ . '/../../autoload.php',        // when installed as a dependency
];

foreach ($possibleAutoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

LightWeight\CLI\CLI::bootstrap(__DIR__)->run();
