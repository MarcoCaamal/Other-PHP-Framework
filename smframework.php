<?php

require_once './vendor/autoload.php';

use SMFramework\Database\DatabaseDriverContract;
use SMFramework\Database\Migrations\Migrator;
use SMFramework\Database\PdoDriver;

$driver = singleton(DatabaseDriverContract::class, PdoDriver::class);



$migrator = new Migrator(
    __DIR__ . '/database/migrations',
    __DIR__ . '/templates',
    $driver
);

if ($argv[1] == "make:migration") {
    $migrator->make($argv[2]);
} elseif ($argv[1] == "migrate") {
    $migrator->migrate();
}
