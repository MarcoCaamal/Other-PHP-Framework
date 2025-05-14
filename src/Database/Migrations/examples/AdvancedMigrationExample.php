<?php

namespace LightWeight\Database\Migrations;

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->enum('role', ['user', 'admin', 'editor'])->default('user');
            $table->integer('login_count')->default(0)->unsigned()->comment('Number of times user has logged in');
            $table->timestamps();
            
            // Create indexes
            $table->index(['name', 'email'], 'name_email_idx');
        });
        
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('user_id');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->datetime('published_at')->nullable();
            $table->timestamps();
            
            // Add foreign key
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('post_id');
            $table->boolean('approved')->default(false);
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('post_id')->references('id')->on('posts');
        });
    }
    
    public function down()
    {
        // Drop tables in reverse order due to foreign key constraints
        Schema::dropIfExists('comments');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};
