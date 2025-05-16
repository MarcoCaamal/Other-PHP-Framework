# Acciones de Claves Foráneas en las Migraciones de LightWeight

> 🌐 [English Documentation](../en/foreign-key-actions.md)

El sistema de migraciones de LightWeight ahora soporta la especificación de acciones referenciales para las restricciones de claves foráneas, dándote control total sobre cómo tu base de datos mantiene la integridad referencial.

## ¿Qué son las Acciones de Claves Foráneas?

Las acciones de claves foráneas (como `CASCADE`, `SET NULL`, etc.) determinan cómo debe reaccionar la base de datos cuando se actualiza o elimina una fila referenciada. Estas acciones ayudan a mantener la integridad referencial en tu base de datos.

## Acciones Disponibles

LightWeight soporta las siguientes acciones referenciales:

- `CASCADE`: Cuando se elimina o actualiza una fila referenciada, las filas dependientes correspondientes se eliminan o actualizan automáticamente.
- `SET NULL`: Cuando se elimina o actualiza una fila referenciada, las columnas de clave foránea en las filas dependientes se establecen en NULL.
- `RESTRICT`: Evita la eliminación o actualización de filas referenciadas.
- `NO ACTION`: Similar a RESTRICT, pero la verificación se realiza después de intentar modificar todas las filas.
- `SET DEFAULT`: Cuando se elimina o actualiza una fila referenciada, las columnas de clave foránea en las filas dependientes se establecen en sus valores predeterminados.

## Uso

Puedes especificar acciones de clave foránea usando los métodos `onDelete()` y `onUpdate()` en tus migraciones:

```php
$table->foreign('user_id')
      ->references('id')
      ->onDelete('CASCADE')  // Especifica la acción ON DELETE
      ->onUpdate('CASCADE')  // Especifica la acción ON UPDATE
      ->on('users');
```

> **Nota**: Es importante llamar a los métodos `onDelete()` y `onUpdate()` antes del método `on()`, ya que el método `on()` finaliza la definición de la clave foránea.

## Casos de Uso Comunes

### Eliminaciones en Cascada

Cuando quieres eliminar automáticamente los registros hijos cuando se elimina un registro padre:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('CASCADE')
      ->on('posts');
```

### Establecer NULL al Eliminar el Padre

Cuando quieres que los registros hijos tengan su clave foránea establecida en NULL cuando se elimina el padre:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('SET NULL')
      ->on('posts');
```

> **Importante**: La columna debe permitir valores NULL para que `SET NULL` funcione.

### Prevenir la Eliminación de Registros Referenciados

Cuando quieres evitar la eliminación de registros padre que aún están siendo referenciados:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('RESTRICT')
      ->on('posts');
```

## Uso Avanzado: Múltiples Acciones

Puedes combinar diferentes acciones para operaciones de eliminación y actualización:

```php
$table->foreign('post_id')
      ->references('id')
      ->onDelete('SET NULL')
      ->onUpdate('CASCADE')
      ->on('posts');
```

Esta configuración:
- Establecerá la clave foránea en NULL cuando se elimine el registro padre
- Actualizará automáticamente la clave foránea cuando cambie el ID del padre

## Notas de Implementación

- El orden de llamada de los métodos es importante. Siempre llama a `onDelete()` y `onUpdate()` antes de `on()`.
- Los nombres de las claves foráneas se generan automáticamente e incluyen información sobre las acciones para evitar colisiones.
- El sistema valida las acciones referenciales para asegurar que sean acciones referenciales válidas de MySQL.

## Ejemplo

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->integer('post_id')->nullable();
    $table->integer('user_id')->nullable();
    
    // El comentario se eliminará cuando se elimine la publicación
    $table->foreign('post_id')
          ->references('id')
          ->onDelete('CASCADE')
          ->on('posts');
    
    // El user_id del comentario se establecerá en NULL cuando se elimine el usuario
    $table->foreign('user_id')
          ->references('id')
          ->onDelete('SET NULL')
          ->on('users');
});
```
