# Guide to Creating Migrations in LightWeight

## Introduction

Migrations are a way to manage changes to the database structure in a controlled and versioned manner. LightWeight Framework provides a simple but effective migration system that allows you to safely create, modify, and delete tables and columns.

## Creating a Migration

### Generating a Migration File

To create a new migration, use the CLI command:

```bash
php light.php make:migration create_users_table
```

This will generate a file in the migrations directory with a timestamp-based naming format.

### Basic Structure of a Migration

Each migration contains two main methods:

1. `up()`: Defines the changes to be applied to the database.
2. `down()`: Defines how to undo the changes (rollback).

Example:

```php
<?php

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
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
```

## Creating Tables

### Basic Table Creation

To create a table, use the `Schema::create` method in the `up()` method:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});
```

### Available Column Types

LightWeight provides many column types that you can use to define your table structure:

```php
// Primary key (auto-incrementing)
$table->id();
$table->bigId();

// String columns
$table->string('name');                     // VARCHAR(255)
$table->string('name', 100);                // VARCHAR(100)
$table->text('description');                // TEXT
$table->mediumText('description');          // MEDIUMTEXT
$table->longText('content');                // LONGTEXT

// Numeric columns
$table->integer('count');                   // INTEGER
$table->bigInteger('big_count');            // BIGINT
$table->decimal('price', 8, 2);             // DECIMAL(8, 2)
$table->float('amount', 8, 2);              // FLOAT(8, 2)

// Boolean columns
$table->boolean('confirmed');               // BOOLEAN (TINYINT(1))

// Date and time columns
$table->date('birth_date');                 // DATE
$table->time('opening_time');               // TIME
$table->dateTime('completed_at');           // DATETIME
$table->timestamp('processed_at');          // TIMESTAMP
$table->timestamps();                       // created_at and updated_at columns

// Other columns
$table->binary('data');                     // BLOB
$table->enum('level', ['easy', 'medium', 'hard']); // ENUM
$table->json('options');                    // JSON
```

### Column Modifiers

You can apply additional modifiers to columns:

```php
$table->string('email')->unique();           // Add unique constraint
$table->integer('price')->unsigned();        // Add unsigned modifier
$table->string('slug')->nullable();          // Allow NULL values
$table->text('description')->default('...');  // Set default value
$table->timestamp('created_at')->useCurrent(); // Use CURRENT_TIMESTAMP
```

### Auto-Increment and Primary Keys

By default, `id()` creates an auto-increment primary key column:

```php
$table->id();  // Creates bigint AUTO_INCREMENT PRIMARY KEY
```

For custom primary keys:

```php
$table->integer('user_id')->primary();
```

For composite primary keys:

```php
$table->primary(['user_id', 'role_id']);
```

## Foreign Keys

### Basic Foreign Key

To add a foreign key, use the `foreign()` method:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('title');
    $table->text('content');
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users');
});
```

### Foreign Key with Actions

You can specify the behavior for ON DELETE and ON UPDATE:

```php
$table->foreign('user_id')
      ->references('id')
      ->on('users')
      ->onDelete('cascade')
      ->onUpdate('cascade');
```

Available actions:
- `cascade`: When the referenced row is deleted/updated, delete/update the dependent rows
- `restrict`: Prevent deletion/updating of the referenced row
- `set null`: Set the column to NULL when the referenced row is deleted/updated
- `no action`: Similar to restrict (but check timing differs)

### Foreign Key Shortcuts

LightWeight provides shorthand methods for commonly used foreign key patterns:

```php
$table->foreignId('user_id')->constrained();

// Same as:
$table->unsignedBigInteger('user_id');
$table->foreign('user_id')->references('id')->on('users');
```

## Indexes

### Adding Indexes

```php
// Add a simple index
$table->index('email');

// Add a composite index
$table->index(['account_id', 'created_at']);

// Add unique index
$table->unique('email');

// Add unique composite index
$table->unique(['account_id', 'slug']);
```

### Index Names

By default, LightWeight generates index names for you, but you can specify a custom name:

```php
$table->index('email', 'users_email_index');
$table->unique(['account_id', 'slug'], 'account_slug_unique');
```

## Modifying Tables

### Adding Columns

To add columns to an existing table, use the `Schema::table` method:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone_number')->nullable()->after('email');
});
```

The `after()` method specifies the position of the new column.

### Modifying Columns

For column modifications, you'll need to use the `change()` method:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('name', 50)->change();  // Change length to 50
    $table->string('email')->nullable()->change();  // Make nullable
});
```

### Renaming Columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('email', 'email_address');
});
```

### Dropping Columns

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('phone_number');
    // Or drop multiple columns
    $table->dropColumn(['address', 'city', 'zip']);
});
```

### Dropping Indexes

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropIndex('users_email_index');
    // Or using array syntax
    $table->dropIndex(['email']);
});
```

## Running Migrations

### Running All Pending Migrations

```bash
php light.php migrate
```

### Rolling Back Migrations

To roll back the last batch of migrations:

```bash
php light.php migrate:rollback
```

To roll back a specific number of migrations:

```bash
php light.php migrate:rollback --step=2
```

To roll back all migrations:

```bash
php light.php migrate:reset
```

### Refreshing Database (Drop and Re-create)

```bash
php light.php migrate:refresh
```

## Best Practices

1. **Keep migrations focused**: Each migration should perform a specific task.
2. **Make migrations reversible**: Always implement the `down()` method properly.
3. **Test migrations**: Test both the `up()` and `down()` methods before deploying.
4. **Use timestamps**: Let the migration system generate timestamps for file names.
5. **Be explicit**: Use explicit column types and lengths.
6. **Use foreign keys**: They help maintain database integrity.
7. **Document complex migrations**: Add comments to explain complex changes.

## Advanced Usage

### Raw SQL in Migrations

You can use raw SQL statements when needed:

```php
DB::statement('CREATE FULLTEXT INDEX fulltext_index ON posts(title, content)');
```

### Creating Views

```php
public function up()
{
    DB::statement('
        CREATE VIEW active_users AS
        SELECT * FROM users WHERE active = 1
    ');
}

public function down()
{
    DB::statement('DROP VIEW IF EXISTS active_users');
}
```

## Related Topics

- [Database Transactions](database-transactions.md)
- [Schema Builder Reference](migrations-schema-builder.md)
- [Foreign Key Actions](foreign-key-actions.md)
- [Migration Best Practices](migration-best-practices.md)
