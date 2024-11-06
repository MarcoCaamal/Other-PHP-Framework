<?php

use LightWeight\Database\DB;
use LightWeight\Database\Migrations\Contracts\MigrationContract;

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
