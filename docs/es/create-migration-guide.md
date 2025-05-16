# Guía para Crear Migraciones en LightWeight

## Introducción

Las migraciones son una forma de gestionar los cambios en la estructura de la base de datos de manera controlada y versionada. LightWeight Framework proporciona un sistema de migraciones simple pero efectivo que permite crear, modificar y eliminar tablas y columnas de forma segura.

## Creación de una Migración

### Generar un Archivo de Migración

Para crear una nueva migración, utiliza el comando CLI:

```bash
php light.php make:migration crear_tabla_usuarios
```

Esto generará un archivo en el directorio de migraciones con un formato de nombre que incluye una marca temporal.

### Estructura Básica de una Migración

Cada migración contiene dos métodos principales:

1. `up()`: Define los cambios que se aplicarán a la base de datos.
2. `down()`: Define cómo deshacer los cambios (rollback).

Ejemplo:

```php
<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
```

## Creación de Tablas

### Creación Básica de Tablas

Para crear una tabla, utiliza el método `Schema::create` en el método `up()`:

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('email');
    $table->timestamps();
});
```

### Tipos de Columnas Disponibles

LightWeight proporciona muchos tipos de columnas que puedes usar para definir la estructura de tu tabla:

```php
// Clave primaria (auto-incremento)
$table->id();
$table->bigId();

// Columnas de texto
$table->string('nombre');                   // VARCHAR(255)
$table->string('nombre', 100);              // VARCHAR(100)
$table->text('descripcion');                // TEXT
$table->mediumText('descripcion');          // MEDIUMTEXT
$table->longText('contenido');              // LONGTEXT

// Columnas numéricas
$table->integer('contador');                // INTEGER
$table->bigInteger('contador_grande');      // BIGINT
$table->decimal('precio', 8, 2);            // DECIMAL(8, 2)
$table->float('cantidad', 8, 2);            // FLOAT(8, 2)

// Columnas booleanas
$table->boolean('confirmado');              // BOOLEAN (TINYINT(1))

// Columnas de fecha y hora
$table->date('fecha_nacimiento');           // DATE
$table->time('hora_apertura');              // TIME
$table->dateTime('completado_en');          // DATETIME
$table->timestamp('procesado_en');          // TIMESTAMP
$table->timestamps();                       // Columnas created_at y updated_at

// Otras columnas
$table->binary('datos');                    // BLOB
$table->enum('nivel', ['fácil', 'medio', 'difícil']); // ENUM
$table->json('opciones');                   // JSON
```

### Modificadores de Columnas

Puedes aplicar modificadores adicionales a las columnas:

```php
$table->string('email')->unique();           // Añadir restricción única
$table->integer('precio')->unsigned();        // Añadir modificador unsigned
$table->string('slug')->nullable();          // Permitir valores NULL
$table->text('descripcion')->default('...');  // Establecer valor por defecto
$table->timestamp('created_at')->useCurrent(); // Usar CURRENT_TIMESTAMP
```

### Auto-Incremento y Claves Primarias

Por defecto, `id()` crea una columna de clave primaria auto-incremental:

```php
$table->id();  // Crea bigint AUTO_INCREMENT PRIMARY KEY
```

Para claves primarias personalizadas:

```php
$table->integer('usuario_id')->primary();
```

Para claves primarias compuestas:

```php
$table->primary(['usuario_id', 'rol_id']);
```

## Claves Foráneas

### Clave Foránea Básica

Para añadir una clave foránea, utiliza el método `foreign()`:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('usuario_id');
    $table->string('titulo');
    $table->text('contenido');
    $table->timestamps();
    
    $table->foreign('usuario_id')->references('id')->on('usuarios');
});
```

### Clave Foránea con Acciones

Puedes especificar el comportamiento para ON DELETE y ON UPDATE:

```php
$table->foreign('usuario_id')
      ->references('id')
      ->on('usuarios')
      ->onDelete('cascade')
      ->onUpdate('cascade');
```

Acciones disponibles:
- `cascade`: Cuando la fila referenciada es eliminada/actualizada, elimina/actualiza las filas dependientes
- `restrict`: Impide la eliminación/actualización de la fila referenciada
- `set null`: Establece la columna a NULL cuando la fila referenciada es eliminada/actualizada
- `no action`: Similar a restrict (pero el momento de verificación difiere)

### Atajos para Claves Foráneas

LightWeight proporciona métodos abreviados para patrones de claves foráneas comúnmente utilizados:

```php
$table->foreignId('usuario_id')->constrained();

// Es lo mismo que:
$table->unsignedBigInteger('usuario_id');
$table->foreign('usuario_id')->references('id')->on('usuarios');
```

## Índices

### Añadir Índices

```php
// Añadir un índice simple
$table->index('email');

// Añadir un índice compuesto
$table->index(['cuenta_id', 'created_at']);

// Añadir índice único
$table->unique('email');

// Añadir índice compuesto único
$table->unique(['cuenta_id', 'slug']);
```

### Nombres de Índices

Por defecto, LightWeight genera nombres de índices para ti, pero puedes especificar un nombre personalizado:

```php
$table->index('email', 'usuarios_email_index');
$table->unique(['cuenta_id', 'slug'], 'cuenta_slug_unique');
```

## Modificando Tablas

### Añadir Columnas

Para añadir columnas a una tabla existente, utiliza el método `Schema::table`:

```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->string('telefono')->nullable()->after('email');
});
```

El método `after()` especifica la posición de la nueva columna.

### Modificar Columnas

Para modificaciones de columnas, necesitarás usar el método `change()`:

```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->string('nombre', 50)->change();  // Cambiar longitud a 50
    $table->string('email')->nullable()->change();  // Hacer nullable
});
```

### Renombrar Columnas

```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->renameColumn('email', 'correo_electronico');
});
```

### Eliminar Columnas

```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->dropColumn('telefono');
    // O eliminar múltiples columnas
    $table->dropColumn(['direccion', 'ciudad', 'codigo_postal']);
});
```

### Eliminar Índices

```php
Schema::table('usuarios', function (Blueprint $table) {
    $table->dropIndex('usuarios_email_index');
    // O usando sintaxis de array
    $table->dropIndex(['email']);
});
```

## Ejecutando Migraciones

### Ejecutar Todas las Migraciones Pendientes

```bash
php light.php migrate
```

### Revertir Migraciones

Para revertir el último lote de migraciones:

```bash
php light.php migrate:rollback
```

Para revertir un número específico de migraciones:

```bash
php light.php migrate:rollback --step=2
```

Para revertir todas las migraciones:

```bash
php light.php migrate:reset
```

### Refrescar la Base de Datos (Eliminar y Volver a Crear)

```bash
php light.php migrate:refresh
```

## Mejores Prácticas

1. **Mantén las migraciones enfocadas**: Cada migración debe realizar una tarea específica.
2. **Haz las migraciones reversibles**: Implementa siempre el método `down()` adecuadamente.
3. **Prueba las migraciones**: Prueba tanto el método `up()` como el `down()` antes de implementarlos.
4. **Usa marcas temporales**: Deja que el sistema de migraciones genere marcas temporales para los nombres de archivo.
5. **Sé explícito**: Utiliza tipos y longitudes de columnas explícitos.
6. **Usa claves foráneas**: Ayudan a mantener la integridad de la base de datos.
7. **Documenta migraciones complejas**: Añade comentarios para explicar cambios complejos.

## Uso Avanzado

### SQL Directo en Migraciones

Puedes usar declaraciones SQL directas cuando sea necesario:

```php
DB::statement('CREATE FULLTEXT INDEX fulltext_index ON posts(titulo, contenido)');
```

### Creación de Vistas

```php
public function up()
{
    DB::statement('
        CREATE VIEW usuarios_activos AS
        SELECT * FROM usuarios WHERE activo = 1
    ');
}

public function down()
{
    DB::statement('DROP VIEW IF EXISTS usuarios_activos');
}
```

## Temas Relacionados

- [Transacciones de Base de Datos](database-transactions.md)
- [Referencia del Constructor de Esquemas](migrations-schema-builder.md)
- [Acciones de Claves Foráneas](foreign-key-actions.md)
- [Mejores Prácticas para Migraciones](migration-best-practices.md)
