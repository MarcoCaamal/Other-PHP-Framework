<?php

use SMFramework\Database\Migrations\Contracts\MigrationContract;
use SMFramework\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            passwordHash VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )ENGINE=innodb");
    }
    public function down()
    {
        DB::statement("DROP TABLE users");
    }
};
