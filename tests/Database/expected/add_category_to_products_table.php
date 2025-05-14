<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category');
        });
    }
    
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
