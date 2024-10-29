<?php

use SMFramework\Database\Migrations\Contracts\MigrationContract;
use SMFramework\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("\$UP");
    }
    public function down()
    {
        DB::statement("\$DOWN");
    }
};
