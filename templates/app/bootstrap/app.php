<?php

use Dotenv\Dotenv;
use LightWeight\Application;

require_once __DIR__ . '/../vendor/autoload.php';

if (is_file(__DIR__ . '/../.env')) {
    // Load environment variables from .env file
    Dotenv::createImmutable(__DIR__ . '/..')->load();
}

return Application::bootstrap(__DIR__ . '/..');
