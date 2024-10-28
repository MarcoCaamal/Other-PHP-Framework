<?php

require_once './vendor/autoload.php';

use SMFramework\Database\DatabaseDriverContract;
use SMFramework\Database\Migrations\Migrator;
use SMFramework\Database\PdoDriver;

$driver = singleton(DatabaseDriverContract::class, PdoDriver::class);
$driver->connect('mysql', 'localhost', 3306, 'smframework', 'root', '');

$migrator = new Migrator(
    __DIR__ . '/database/migrations',
    __DIR__ . '/templates',
    $driver
);

if ($argv[1] == "make:migration") {
    $migrator->make($argv[2]);
} elseif ($argv[1] == "migrate") {
    $migrator->migrate();
} else if ($argv[1] == "rollback") {
    $step = null;
    if (count($argv) == 4 && $argv[2] == "--step") {
        $step = $argv[3];
    }
    $migrator->rollback($step);
}
