# Foreign Key Actions in LightWeight Migrations

> 🌐 [Documentación en Español](../es/foreign-key-actions.md)

LightWeight's migration system now supports specifying referential actions for foreign key constraints, giving you full control over how your database maintains referential integrity.

## What are Foreign Key Actions?

Foreign key actions (like `CASCADE`, `SET NULL`, etc.) determine how the database should react when a referenced row is updated or deleted. These actions help maintain referential integrity in your database.

## Available Actions

LightWeight supports the following referential actions:

- `CASCADE`: When a referenced row is deleted or updated, the corresponding dependent rows are automatically deleted or updated.
- `SET NULL`: When a referenced row is deleted or updated, the foreign key columns in dependent rows are set to NULL.
- `RESTRICT`: Prevents the deletion or update of referenced rows.
- `NO ACTION`: Similar to RESTRICT, but the check is performed after trying to modify all rows.
- `SET DEFAULT`: When a referenced row is deleted or updated, the foreign key columns in dependent rows are set to their default values.

## Usage

You can specify foreign key actions using the `onDelete()` and `onUpdate()` methods in your migrations:

```php
$table->foreign('user_id')
      ->references('id')
      ->onDelete('CASCADE')  // Specify ON DELETE action
      ->onUpdate('CASCADE')  // Specify ON UPDATE action
      ->on('users');
```

> **Note**: It's important to call `onDelete()` and `onUpdate()` before the `on()` method, as the `on()` method finalizes the foreign key definition.

## Common Use Cases

### Cascading Deletes

When you want to automatically delete child records when a parent record is deleted:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('CASCADE')
      ->on('posts');
```

### Setting NULL on Parent Deletion

When you want child records to have their foreign key set to NULL when the parent is deleted:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('SET NULL')
      ->on('posts');
```

> **Important**: The column must be nullable for `SET NULL` to work.

### Preventing Deletion of Referenced Records

When you want to prevent deletion of parent records that are still referenced:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('RESTRICT')
      ->on('posts');
```

## Advanced Usage: Multiple Actions

You can combine different actions for delete and update operations:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('SET NULL')
      ->onUpdate('CASCADE')
      ->on('posts');
```

This configuration will:
- Set the foreign key to NULL when the parent record is deleted
- Automatically update the foreign key when the parent ID changes

## Implementation Notes

- The order of method calls is important. Always call `onDelete()` and `onUpdate()` before `on()`.
- Foreign key names are automatically generated and include information about the actions to prevent collisions.
- The system validates referential actions to ensure they're valid MySQL referential actions.

## Example

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->integer('post_id')->nullable();
    $table->integer('user_id')->nullable();
    
    // Comment will be deleted when post is deleted
    $table->foreign('post_id')
          ->references('id')
          ->onDelete('CASCADE')
          ->on('posts');
    
    // Comment's user_id will be set to NULL when user is deleted
    $table->foreign('user_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->on('users');
});
```
