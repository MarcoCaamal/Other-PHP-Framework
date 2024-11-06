<?php

namespace LightWeight\Database\Migrations\Contracts;

interface MigrationContract
{
    public function up();
    public function down();
}
