# Foreign Key Actions: Practical Examples

> 🌐 [Documentación en Español](../es/foreign-key-actions-examples.md)

This document provides practical examples of how to use referential actions (`onDelete` and `onUpdate`) in LightWeight's migration system for foreign keys.

## Example 1: Blog with Cascade Deletion

In this example, we have a blog where we want all posts to be automatically deleted when a user is removed.

```php
// Migration to create the users table
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->timestamps();
});

// Migration to create the posts table with cascade deletion
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->integer('user_id');
    $table->timestamps();
    
    $table->foreign('user_id')
          ->references('id')
          ->onDelete('CASCADE')  // When the user is deleted, all their posts are deleted
          ->on('users');
});
```

## Example 2: Inventory System with SET NULL

In this example, if a supplier is deleted, their products remain but lose their association.

```php
// Migration to create the suppliers table
Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('contact_info');
    $table->timestamps();
});

// Migration to create the products table that can exist without a supplier
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->integer('supplier_id')->nullable();  // Must be nullable for SET NULL
    $table->timestamps();
    
    $table->foreign('supplier_id')
          ->references('id')
          ->onDelete('SET NULL')  // When the supplier is deleted, the product loses its association
          ->on('suppliers');
});
```

## Example 3: Academic System with RESTRICT

In this example, a department cannot be deleted if it has associated professors.

```php
// Migration to create the departments table
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// Migration to create the professors table that prevent their department from being deleted
Schema::create('professors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('department_id');
    $table->timestamps();
    
    $table->foreign('department_id')
          ->references('id')
          ->onDelete('RESTRICT')  // Prevents a department from being deleted if it has professors
          ->on('departments');
});
```

## Example 4: Sales System with Cascade Updates

In this example, if a customer's ID changes, all their orders are automatically updated.

```php
// Migration to create the customers table
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});

// Migration to create the orders table that updates if the customer ID changes
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->integer('customer_id');
    $table->decimal('total', 10, 2);
    $table->timestamps();
    
    $table->foreign('customer_id')
          ->references('id')
          ->onUpdate('CASCADE')  // If the customer ID changes, it updates in the orders
          ->on('customers');
});
```

## Example 5: System with Multiple Referential Actions

In this example, we combine different actions for `onDelete` and `onUpdate`.

```php
// Migration to create a projects table
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// Migration to create a tasks table with different actions
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('description');
    $table->integer('project_id')->nullable();
    $table->integer('assigned_user_id')->nullable();
    $table->timestamps();
    
    // When a project is deleted, tasks are set to NULL
    // but if the project ID is updated, it cascades
    $table->foreign('project_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->onUpdate('CASCADE')
          ->on('projects');
    
    // When a user is deleted, assigned tasks are set to NULL
    $table->foreign('assigned_user_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->on('users');
});
```

## Important Notes

- Always make sure that columns that use `SET NULL` actions are defined as nullable.
- `RESTRICT` and `NO ACTION` behave similarly but are executed at different times during transaction processing.
- The `CASCADE` action can lead to multiple deletions, so use it carefully in production environments.
- Foreign key actions are enforced by the database engine, not by LightWeight code.
