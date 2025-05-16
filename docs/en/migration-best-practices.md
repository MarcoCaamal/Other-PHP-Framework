# Best Practices for Migrations in LightWeight

> 🌐 [Documentación en Español](../es/migration-best-practices.md)

## Design and Organization

### Migration Atomicity

Each migration should perform a logical and atomic set of changes to the database structure. This makes migrations easier to understand, test, and roll back.

**Recommended:**
- One migration to create a table and its basic indexes
- A separate migration to add relationships between tables
- A specific migration to modify existing columns

**Not Recommended:**
- Modifying several unrelated tables in a single migration
- Mixing structure changes with data insertion

### Consistent Naming

Adopt a consistent naming system for your migrations, tables, columns, and indexes:

**For migration files:**
```
YYYYMMDDHHMMSS_create_users_table.php
YYYYMMDDHHMMSS_add_address_column_to_users.php
YYYYMMDDHHMMSS_create_users_orders_relationship.php
```

**For tables:**
- Use plural nouns: `users`, `products`, `categories`
- For pivot tables, use singular names separated by underscore: `product_category`

**For columns:**
- Use snake_case for column names: `registration_date`, `unit_price`
- Use `id` for primary keys
- Use `{singular_table}_id` for foreign keys: `user_id`, `product_id`

**For indexes:**
- Simple indexes: `{table}_{column}_index`
- Unique indexes: `{table}_{column}_unique`
- Composite indexes: `{table}_{column1}_{column2}_index`

## Implementation

### Always Define the Down() Method

Each migration should include a `down()` method that reverses exactly what the `up()` method does. This ensures you can move backward and forward in the migration history without issues.

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('last_name', 100)->after('name');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('last_name');
    });
}
```

### Consider Performance

Schema operations can be costly, especially on large tables:

1. **Group modifications**: Combine multiple alterations of the same table into a single call to `Schema::table()`.

2. **Be careful with indexes on large tables**: Adding an index to a table with millions of rows can lock the table during creation time.

3. **Consider using lock-free migrations**: For production databases, research techniques to modify schemas without locking tables.

```php
// Better: Group modifications to the same table
Schema::table('products', function (Blueprint $table) {
    $table->string('sku')->after('name');
    $table->decimal('tax', 5, 2)->after('price');
    $table->boolean('featured')->default(false);
});

// Instead of three separate operations
```

### Manage Relationships in the Correct Order

When creating or removing relationships between tables:

1. **Creation**: First create the main tables, then the dependent tables, and finally add the foreign keys.
2. **Deletion**: First remove the foreign keys, then the dependent tables, and finally the main tables.

```php
// In the up() method
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->unsignedBigInteger('category_id');
    $table->timestamps();
    
    $table->foreign('category_id')->references('id')->on('categories');
});

// In the down() method
Schema::dropIfExists('products'); // First the table with the foreign key
Schema::dropIfExists('categories'); // Then the referenced table
```

### Define Precise Column Types

Use the most appropriate column type for each case to optimize performance and storage:

```php
// Specific types according to usage
$table->string('postal_code', 10); // Limited length
$table->tinyInteger('age')->unsigned(); // Range 0-255 is sufficient
$table->decimal('price', 10, 2); // Monetary precision
$table->enum('status', ['pending', 'processing', 'completed', 'cancelled']);
$table->text('description'); // For long text without specific limit
```

### Use Default Values and NOT NULL Constraints

Clearly define your column constraints:

```php
// With well-defined constraints
$table->string('name')->nullable(false);
$table->boolean('active')->default(true);
$table->dateTime('registration_date')->useCurrent();
$table->integer('attempts')->default(0);
```

## Management and Deployment

### Never Modify a Published Migration

Once a migration has been applied in any environment (especially in production), you should never modify its content. Instead, create a new migration that makes the additional changes.

**Incorrect:**
- Modifying the file `20220101000000_create_users_table.php` after deploying it

**Correct:**
- Creating a new migration `20220201000000_modify_email_column_in_users.php`

### Test Your Migrations

Before deploying migrations to production:

1. Test applying the migration (`up()`)
2. Test reversing the migration (`down()`)
3. Test reapplying the migration after reverting it
4. Verify data integrity after these cycles

### Safe Migration in Production

For migrations in production environments:

1. **Make a complete backup** of the database before applying migrations
2. **Schedule migrations** during periods of low activity
3. **Plan the downtime** necessary for complex migrations
4. **Have a rollback plan** in case of problems

## Specific Use Cases

### Migrations to Modify Data

Although migrations are primarily used for schema changes, you can also use them to modify data:

```php
public function up()
{
    // Modification of existing data
    DB::table('products')->where('category', 'electronics')
        ->update(['department_id' => 5]);
}

public function down()
{
    // Revert the modification
    DB::table('products')->where('department_id', 5)
        ->update(['category' => 'electronics', 'department_id' => null]);
}
```

### Conditional Migrations

In some cases, you may need a migration to behave differently based on certain conditions:

```php
public function up()
{
    if (!Schema::hasColumn('users', 'last_name')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('name')->nullable();
        });
    }
}
```

### Working with JSON Columns

For databases that support JSON:

```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->json('value');
    $table->timestamps();
});
```

## Security and Validation

### Name Validation

Always validate table and column names to avoid SQL injection problems:

```php
// Validate table names
if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
    throw new InvalidArgumentException("Invalid table name: $tableName");
}

// Never use dynamic names without validation
$columnName = $request->input('column'); // Dangerous without validation
```

### Protection of Sensitive Data

Take special care with columns that store sensitive data:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->string('password'); // Will be stored encrypted, not in plain text
    $table->string('api_key')->nullable(); // Sensitive data
    $table->text('profile_json')->nullable();
    $table->timestamps();
    
    // Add comments for sensitive data
    $table->comment = 'Contains personal data subject to privacy regulations';
});
```

## Conclusion

Migrations are a powerful tool for maintaining the evolution of your database schema in a controlled and reproducible way. By following these best practices, you can create a robust and maintainable system that facilitates teamwork and reliable deployments across all your environments.
