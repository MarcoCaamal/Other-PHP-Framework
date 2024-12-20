<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            lastname VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME,
            updated_at DATETIME NULL
        )ENGINE=innodb");
    }
    public function down()
    {
        DB::statement("DROP TABLE users");
    }
};
