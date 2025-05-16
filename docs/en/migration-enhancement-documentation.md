# LightWeight Migration System Enhancements

> 🌐 [Documentación en Español](../es/migration-enhancement-documentation.md)

## Introduction

This document describes the new functionalities added to the LightWeight framework's migration system to enable advanced database schema manipulation operations, including renaming tables, columns, and indexes, as well as modifying column types.

## New Features

### 1. Renaming Tables

A new static method `Schema::rename()` has been added that allows renaming existing tables in the database.

#### Syntax
```php
Schema::rename(string $from, string $to): void;
```

#### Parameters
- `$from`: Current name of the table
- `$to`: New name for the table

#### Usage Example
```php
Schema::rename('users', 'app_users');
```

### 2. Renaming Columns

It is now possible to rename existing columns within a table using the `renameColumn()` method in the `Blueprint` class.

#### Syntax
```php
public function renameColumn(string $from, string $to, ?string $type = null, array $options = []): self;
```

#### Parameters
- `$from`: Current name of the column
- `$to`: New name for the column
- `$type`: (Optional) Data type for the column (VARCHAR, INT, etc.)
- `$options`: (Optional) Additional options such as length, precision, etc.

#### Usage Example
```php
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('name', 'full_name');
    
    // With specific type
    $table->renameColumn('description', 'bio', 'TEXT');
    
    // With options
    $table->renameColumn('email', 'contact_email', 'VARCHAR', ['length' => 100]);
});
```

### 3. Modifying Columns

The `change()` method has been implemented to modify the type or properties of an existing column.

#### Syntax
```php
public function change(string $column, string $type, array $parameters = []): self;
```

#### Parameters
- `$column`: Name of the column to modify
- `$type`: New type for the column
- `$parameters`: Additional parameters for the column type

#### Usage Example
```php
Schema::table('products', function (Blueprint $table) {
    // Change column type
    $table->change('price', 'DECIMAL', [
        'precision' => 10, 
        'scale' => 2
    ]);
    
    // Change VARCHAR column length
    $table->change('name', 'VARCHAR', ['length' => 100]);
    
    // Change column type to TEXT
    $table->change('description', 'TEXT');
    
    // Helper methods for nullable/not null
    $table->changeToNullable('optional_field');
    $table->changeToNotNull('required_field');
});
```

### 4. Renaming Indexes

The `renameIndex()` method allows renaming existing indexes on a table.

#### Syntax
```php
public function renameIndex(string $from, string $to): self;
```

#### Parameters
- `$from`: Current name of the index
- `$to`: New name for the index

#### Usage Example
```php
Schema::table('posts', function (Blueprint $table) {
    $table->renameIndex('posts_user_id_index', 'posts_author_id_index');
});
```

## MySQL Compatibility

These operations are compatible with MySQL and follow best practices for schema manipulation. Renaming and modification operations preserve existing data in the tables.

## Important Notes

- When renaming columns, it is necessary to specify the data type if you want to maintain the same type.
- Column modification operations can take time on large tables and should be performed with caution.
- It is recommended to make backups before performing schema operations in production environments.
- These functionalities are useful for refactoring and schema evolution without the need to recreate tables.
