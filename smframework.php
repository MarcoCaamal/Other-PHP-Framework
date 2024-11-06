<?php

require_once './vendor/autoload.php';

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Migrations\Migrator;
use LightWeight\Database\PdoDriver;

$driver = singleton(DatabaseDriverContract::class, PdoDriver::class);
$driver->connect('mysql', 'localhost', 3306, 'LightWeight', 'root', '');

$migrator = new Migrator(
    __DIR__ . '/database/migrations',
    __DIR__ . '/templates',
    $driver
);

if ($argv[1] == "make:migration") {
    $migrator->make($argv[2]);
} elseif ($argv[1] == "migrate") {
    $migrator->migrate();
} elseif ($argv[1] == "rollback") {
    $step = null;
    if (count($argv) == 4 && $argv[2] == "--step") {
        $step = $argv[3];
    }
    $migrator->rollback($step);
}
