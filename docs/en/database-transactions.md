# Database Transactions

## Introduction

Transactions are a fundamental concept in database management that allow grouping multiple operations to execute as a single atomic unit. This ensures that either all operations complete successfully, or none of them have any effect in case of an error. LightWeight Framework provides a simple API for working with transactions.

## Basic Concepts

A transaction has four fundamental properties known as ACID:

1. **Atomicity**: All operations execute or none execute.
2. **Consistency**: The database moves from one valid state to another valid state.
3. **Isolation**: The effects of a transaction are not visible to other transactions until it is committed.
4. **Durability**: Once committed, a transaction persists even in the event of system failures.

## Transactions API

LightWeight offers a simple interface for working with transactions through the `DB` class.

### Basic Transactions

```php
use LightWeight\Database\DB;

// Start a transaction
DB::beginTransaction();

try {
    // Perform database operations
    DB::table('accounts')->where('id', 1)->update(['balance' => DB::raw('balance - 100')]);
    DB::table('accounts')->where('id', 2)->update(['balance' => DB::raw('balance + 100')]);
    
    // Commit the transaction
    DB::commit();
} catch (\Exception $e) {
    // If any error occurs, rollback the transaction
    DB::rollback();
    
    // Handle the exception
    throw $e;
}
```

## Common Use Cases

### Financial Transfers

A classic use case for transactions is transferring funds between accounts:

```php
// Start transaction
DB::beginTransaction();

try {
    // Verify sufficient funds
    $sourceAccount = DB::table('accounts')->where('id', $sourceAccountId)->first();
    
    if ($sourceAccount['balance'] < $amount) {
        throw new \Exception('Insufficient funds');
    }
    
    // Withdraw from source account
    DB::table('accounts')
        ->where('id', $sourceAccountId)
        ->update(['balance' => DB::raw("balance - $amount")]);
    
    // Deposit to destination account
    DB::table('accounts')
        ->where('id', $destinationAccountId)
        ->update(['balance' => DB::raw("balance + $amount")]);
    
    // Record the transaction
    DB::table('movements')->insert([
        'source_account' => $sourceAccountId,
        'destination_account' => $destinationAccountId,
        'amount' => $amount,
        'date' => date('Y-m-d H:i:s'),
        'description' => 'Transfer'
    ]);
    
    // Commit transaction
    DB::commit();
} catch (\Exception $e) {
    // Rollback transaction in case of error
    DB::rollback();
    throw $e;
}
```

### User Registration with Profile

Another common case is creating a user along with their associated profile:

```php
DB::beginTransaction();

try {
    // Create user
    DB::table('users')->insert([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $userId = DB::lastInsertId();
    
    // Create related profile
    DB::table('profiles')->insert([
        'user_id' => $userId,
        'biography' => $data['biography'] ?? null,
        'website' => $data['website'] ?? null,
        'avatar' => 'default.png'
    ]);
    
    // Assign default roles
    DB::table('user_roles')->insert([
        'user_id' => $userId,
        'role_id' => 2 // Default "user" role
    ]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

## Best Practices

### 1. Keep Transactions Short

Transactions maintain locks on the database, which can impact performance. Try to keep them as brief as possible.

### 2. Catch Specific Exceptions

Catch specific types of exceptions to handle different error cases:

```php
try {
    DB::beginTransaction();
    
    // Code that might fail
    
    DB::commit();
} catch (\PDOException $e) {
    DB::rollback();
    // Handle database error
} catch (\Exception $e) {
    DB::rollback();
    // Handle other errors
}
```

### 3. Avoid External Operations

Don't perform external operations (such as API calls or long-running tasks) within a transaction:

```php
// Bad: External operation within transaction
DB::beginTransaction();
try {
    DB::table('orders')->insert(['customer_id' => 1]);
    $result = ExternalService::checkInventory(); // Bad practice!
    DB::table('inventory')->update(['stock' => DB::raw('stock - 1')]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}

// Good: External operation outside the transaction
$result = ExternalService::checkInventory();
if ($result->hasStock) {
    DB::beginTransaction();
    try {
        DB::table('orders')->insert(['customer_id' => 1]);
        DB::table('inventory')->update(['stock' => DB::raw('stock - 1')]);
        DB::commit();
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 4. Use Appropriate Isolation Levels

Isolation levels determine how multiple concurrent transactions behave. You can configure this using direct SQL statements:

```php
// Set isolation level for a specific transaction
DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
DB::beginTransaction();
// Transaction operations
DB::commit();
```

## Isolation Levels

MySQL supports four isolation levels:

1. **READ UNCOMMITTED**: Can read uncommitted data (dirty reads).
2. **READ COMMITTED**: Only reads committed data.
3. **REPEATABLE READ**: Guarantees consistent reads within the transaction (default in InnoDB).
4. **SERIALIZABLE**: The strictest level, prevents concurrency anomalies.

## Conclusion

Transactions are essential for maintaining data integrity in applications that perform multiple related operations in the database. LightWeight offers a simple but effective API for working with transactions. By using them correctly, you can ensure your application maintains a coherent state even in error situations.

> 🌐 [Documentación en Español](../es/database-transactions.md)
