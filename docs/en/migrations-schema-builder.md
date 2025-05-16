# Schema Builder Documentation

> 🌐 [Documentación en Español](../es/migrations-schema-builder.md)

The Schema builder provides a convenient, fluent interface to create and modify database tables. Here's how to use it effectively:

## Creating Tables

To create a new table, use the `Schema::create` method:

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('age');
    $table->boolean('active');
    $table->timestamps();
});
```

## Available Column Types

The Blueprint object provides a variety of column types you can use:

- `$table->id()`: Auto-incrementing ID column
- `$table->bigInteger('column')`: BIGINT column
- `$table->binary('column')`: BLOB column
- `$table->boolean('column')`: TINYINT(1) column
- `$table->date('column')`: DATE column
- `$table->datetime('column')`: DATETIME column
- `$table->decimal('column', $precision, $scale)`: DECIMAL column with precision and scale
- `$table->enum('column', ['option1', 'option2'])`: ENUM column with specified options
- `$table->integer('column')`: INT column
- `$table->json('column')`: JSON column
- `$table->longText('column')`: LONGTEXT column
- `$table->mediumInteger('column')`: MEDIUMINT column
- `$table->mediumText('column')`: MEDIUMTEXT column
- `$table->smallInteger('column')`: SMALLINT column
- `$table->string('column', $length)`: VARCHAR column with optional length (default 255)
- `$table->text('column')`: TEXT column
- `$table->time('column')`: TIME column
- `$table->timestamp('column')`: TIMESTAMP column
- `$table->tinyInteger('column')`: TINYINT column
- `$table->unsignedBigInteger('column')`: UNSIGNED BIGINT column
- `$table->unsignedInteger('column')`: UNSIGNED INT column
- `$table->unsignedSmallInteger('column')`: UNSIGNED SMALLINT column
- `$table->unsignedTinyInteger('column')`: UNSIGNED TINYINT column
- `$table->year('column')`: YEAR column

## Column Modifiers

You can chain modifiers to your column definitions:

```php
$table->string('email')->nullable()->unique();
```

Available modifiers:

- `->autoIncrement()`: Set the column as auto-incrementing
- `->comment('Some comment')`: Add a comment to the column
- `->default($value)`: Set a default value for the column
- `->nullable($value = true)`: Set the column to allow NULL values
- `->unique()`: Add a unique constraint to the column
- `->unsigned()`: Set the column as unsigned (for integer columns)
- `->columnCharset('utf8mb4')`: Set the charset for the column
- `->columnCollation('utf8mb4_unicode_ci')`: Set the collation for the column

## Indexes

You can add indexes to your tables:

```php
// Single column index
$table->index('email');

// Composite index
$table->index(['first_name', 'last_name'], 'name_index');

// Unique index
$table->uniqueIndex('email');

// Primary key
$table->primary('id');
// Or composite primary key
$table->primary(['id', 'type']);
```

## Foreign Keys

To add foreign key constraints:

```php
$table->foreign('user_id')
      ->references('id')
      ->on('users');
```

## Table Operations

Other table operations include:

```php
// Drop table if exists
Schema::dropIfExists('users');

// Modify a table
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
});
```

## Engine, Charset & Collation

You can set engine, charset, and collation for your tables:

```php
$table->engine('InnoDB');
$table->charset('utf8mb4');
$table->collation('utf8mb4_unicode_ci');
```

## Dropping Columns & Indexes

To drop columns:

```php
// Drop a single column
$table->dropColumn('column');

// Drop multiple columns
$table->dropColumn(['column1', 'column2']);
```

To drop indexes:

```php
$table->dropIndex('index_name');
$table->dropPrimary();
$table->dropUnique('unique_index_name');
```

## Timestamps

You can add created_at and updated_at columns in one go:

```php
$table->timestamps();
```

This is equivalent to:

```php
$table->datetime('created_at');
$table->datetime('updated_at')->nullable();
```
