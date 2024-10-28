<?php

use SMFramework\Database\DB;
use SMFramework\Database\Migrations\Contracts\MigrationContract;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("CREATE TABLE products (id INT AUTO_INCREMENT PRIMARY KEY)ENGINE=innodb");
    }
    public function down()
    {
        DB::statement("DROP TABLE products");
    }
};
