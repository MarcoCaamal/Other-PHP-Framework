<?php

use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Drivers\MysqlQueryBuilderDriver;
use LightWeight\Database\QueryBuilder\QueryBuilder;

require __DIR__  . '/vendor/autoload.php';

$driver = new PdoDriver();

$driver->connect('mysql', 'localhost', 3306, 'corona', 'root', '');
$mysql = new MysqlQueryBuilderDriver();
var_dump($mysql->table('categorias')->where('categoriaId', 37)->update([
    'nombre' => 'testUpdated'
]));
debugDie($mysql->getValues());
$builder = new QueryBuilder($mysql, $driver);

var_dump($builder->table('categorias')->where('categoriaId', 37)->update([
    'nombre' => 'testUpdated'
]));

$driver->close();
