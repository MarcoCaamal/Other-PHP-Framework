<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        Schema::create('acceso_usuario', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('acceso_usuario');
    }
};
