# Acciones de Claves Foráneas: Ejemplos Prácticos

> 🌐 [English Documentation](../en/foreign-key-actions-examples.md)

Este documento proporciona ejemplos prácticos sobre cómo utilizar las acciones referenciales (`onDelete` y `onUpdate`) en las claves foráneas del sistema de migraciones de LightWeight.

## Ejemplo 1: Blog con Eliminación en Cascada

En este ejemplo, tenemos un blog donde queremos que al eliminar un usuario, se eliminen automáticamente todas sus publicaciones.

```php
// Migración para crear la tabla de usuarios
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->timestamps();
});

// Migración para crear la tabla de posts con eliminación en cascada
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->integer('user_id');
    $table->timestamps();
    
    $table->foreign('user_id')
          ->references('id')
          ->onDelete('CASCADE')  // Al eliminar el usuario, se eliminan sus posts
          ->on('users');
});
```

## Ejemplo 2: Sistema de Inventario con SET NULL

En este ejemplo, si un proveedor es eliminado, sus productos quedan sin asociación pero no se eliminan.

```php
// Migración para crear la tabla de proveedores
Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('contact_info');
    $table->timestamps();
});

// Migración para crear la tabla de productos que pueden quedar sin proveedor
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->integer('supplier_id')->nullable();  // Debe ser nullable para SET NULL
    $table->timestamps();
    
    $table->foreign('supplier_id')
          ->references('id')
          ->onDelete('SET NULL')  // Al eliminar el proveedor, el producto queda sin asociación
          ->on('suppliers');
});
```

## Ejemplo 3: Sistema Académico con RESTRICT

En este ejemplo, un departamento no puede ser eliminado si tiene profesores asociados.

```php
// Migración para crear la tabla de departamentos
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// Migración para crear la tabla de profesores que impiden eliminar su departamento
Schema::create('professors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('department_id');
    $table->timestamps();
    
    $table->foreign('department_id')
          ->references('id')
          ->onDelete('RESTRICT')  // Impide eliminar un departamento si tiene profesores
          ->on('departments');
});
```

## Ejemplo 4: Sistema de Ventas con Actualizaciones en Cascada

En este ejemplo, si cambia el ID de un cliente, se actualizan automáticamente todas sus órdenes.

```php
// Migración para crear la tabla de clientes
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});

// Migración para crear la tabla de órdenes que se actualizan si cambia el ID del cliente
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->integer('customer_id');
    $table->decimal('total', 10, 2);
    $table->timestamps();
    
    $table->foreign('customer_id')
          ->references('id')
          ->onUpdate('CASCADE')  // Si cambia el ID del cliente, se actualiza en las órdenes
          ->on('customers');
});
```

## Ejemplo 5: Sistema con Múltiples Acciones Referenciales

En este ejemplo combinamos diferentes acciones para `onDelete` y `onUpdate`.

```php
// Migración para crear una tabla de proyectos
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// Migración para crear una tabla de tareas con diferentes acciones
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('description');
    $table->integer('project_id')->nullable();
    $table->integer('assigned_user_id')->nullable();
    $table->timestamps();
    
    // Al eliminar un proyecto, las tareas se establecen a NULL
    // pero si se actualiza el ID del proyecto, se actualiza en cascada
    $table->foreign('project_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->onUpdate('CASCADE')
          ->on('projects');
    
    // Al eliminar un usuario, las tareas asignadas se establecen a NULL
    $table->foreign('assigned_user_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->on('users');
});
```

## Notas Importantes

- Asegúrate siempre de que las columnas que usan acciones `SET NULL` estén definidas como anulables.
- `RESTRICT` y `NO ACTION` se comportan de manera similar pero se ejecutan en diferentes momentos durante el procesamiento de la transacción.
- La acción `CASCADE` puede llevar a múltiples eliminaciones, así que úsala con cuidado en entornos de producción.
- Las acciones de clave foránea son aplicadas por el motor de la base de datos, no por el código de LightWeight.
