<?php

use SMFramework\Database\Migrations\Contracts\MigrationContract;
use SMFramework\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("CREATE TABLE test (id INT AUTO_INCREMENT PRIMARY KEY)ENGINE=innodb");
    }
    public function down()
    {
        DB::statement("DROP TABLE test");
    }
};
