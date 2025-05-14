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

## Ejecutar Migraciones

### Aplicar Migraciones Pendientes

Para ejecutar todas las migraciones pendientes:

```bash
php light.php migrate
```

### Revertir Migraciones

Para revertir la última migración:

```bash
php light.php migrate:rollback
```

Para revertir un número específico de migraciones:

```bash
php light.php migrate:rollback --steps=2
```

## Trabajando con Tablas

### Crear una Tabla

```php
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->decimal('precio', 8, 2);
    $table->text('descripcion')->nullable();
    $table->integer('categoria_id');
    $table->boolean('activo')->default(true);
    $table->timestamps();
});
```

### Modificar una Tabla Existente

```php
Schema::table('productos', function (Blueprint $table) {
    $table->string('sku', 50)->after('nombre');
    $table->index('categoria_id');
});
```

### Eliminar una Tabla

```php
Schema::drop('productos');
// O con verificación de existencia
Schema::dropIfExists('productos');
```

### Renombrar una Tabla

```php
Schema::rename('productos', 'articulos');
```

## Trabajando con Columnas

### Tipos de Columnas Disponibles

| Método | Descripción |
|--------|-------------|
| `$table->id()` | Columna auto-incrementable |
| `$table->bigInteger('cantidad')` | Columna BIGINT |
| `$table->binary('datos')` | Columna BLOB |
| `$table->boolean('confirmado')` | Columna BOOLEAN |
| `$table->date('fecha')` | Columna DATE |
| `$table->dateTime('creado_en')` | Columna DATETIME |
| `$table->decimal('monto', 8, 2)` | Columna DECIMAL con precisión y escala |
| `$table->enum('nivel', ['principiante', 'intermedio', 'avanzado'])` | Columna ENUM con valores permitidos |
| `$table->float('promedio', 8, 2)` | Columna FLOAT con precisión y escala |
| `$table->integer('edad')` | Columna INTEGER |
| `$table->json('opciones')` | Columna JSON |
| `$table->string('nombre', 100)` | Columna VARCHAR con longitud opcional |
| `$table->text('contenido')` | Columna TEXT |
| `$table->time('hora')` | Columna TIME |
| `$table->timestamp('actualizado_en')` | Columna TIMESTAMP |
| `$table->timestamps()` | Añade columnas created_at y updated_at |

### Modificadores de Columnas

Los modificadores se pueden encadenar después de definir una columna:

```php
$table->string('email')->nullable()->default('user@example.com')->unique();
```

Modificadores disponibles:

- `->nullable()` - Permite valores NULL
- `->default($valor)` - Establece un valor por defecto
- `->unique()` - Crea un índice único
- `->index()` - Crea un índice estándar
- `->unsigned()` - Para columnas numéricas, sin signo
- `->comment('texto')` - Añade un comentario a la columna
- `->after('columna')` - Posiciona la columna después de otra

## Trabajando con Índices

### Creación de Índices

```php
// Índice simple
$table->index('email');

// Índice compuesto
$table->index(['account_id', 'created_at']);

// Índice único
$table->unique('email');

// Índice primario
$table->primary('id');
// O compuesto
$table->primary(['id', 'type']);
```

### Eliminar Índices

```php
// Eliminar índice simple
$table->dropIndex('productos_precio_index');

// Eliminar índice único
$table->dropUnique('productos_sku_unique');

// Eliminar índice primario
$table->dropPrimary();
```

## Trabajando con Claves Foráneas

### Crear Claves Foráneas

```php
Schema::table('comentarios', function (Blueprint $table) {
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');
});
```

### Eliminar Claves Foráneas

```php
Schema::table('comentarios', function (Blueprint $table) {
    $table->dropForeign('comentarios_user_id_foreign');
});
```

## Migración Avanzada: Ejemplo Completo

```php
<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        // Tabla de categorías
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('slug', 60)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
        
        // Tabla de posts
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('contenido');
            $table->text('extracto')->nullable();
            $table->enum('estado', ['borrador', 'publicado', 'archivado'])->default('borrador');
            $table->boolean('comentarios_habilitados')->default(true);
            $table->timestamp('publicado_en')->nullable();
            $table->unsignedBigInteger('autor_id');
            $table->unsignedBigInteger('categoria_id');
            $table->timestamps();
            
            // Índices y claves foráneas
            $table->foreign('autor_id')->references('id')->on('users');
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->index('estado');
            $table->index('publicado_en');
        });
        
        // Tabla de etiquetas
        Schema::create('etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30)->unique();
            $table->string('slug', 40)->unique();
            $table->timestamps();
        });
        
        // Tabla pivote para relación muchos a muchos
        Schema::create('post_etiqueta', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('etiqueta_id');
            
            $table->primary(['post_id', 'etiqueta_id']);
            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('etiqueta_id')->references('id')->on('etiquetas');
        });
    }

    public function down()
    {
        // Eliminar en orden inverso para respetar claves foráneas
        Schema::dropIfExists('post_etiqueta');
        Schema::dropIfExists('etiquetas');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categorias');
    }
};
```

## Mejores Prácticas

1. **Nombra las migraciones de forma descriptiva**: Usa nombres que indiquen claramente lo que hace la migración.
2. **Implementa siempre el método down()**: Asegura que cada migración pueda ser revertida.
3. **Organiza las migraciones lógicamente**: Crea primero las tablas que no dependen de otras.
4. **No modifiques migraciones ya ejecutadas**: Crea nuevas migraciones para los cambios adicionales.
5. **Prueba tus migraciones**: Siempre ejecuta y revierte tus migraciones para asegurarte de que funcionan correctamente.
6. **Usa índices adecuadamente**: Añade índices para mejorar el rendimiento, pero sin exagerar.
7. **Documenta tus migraciones**: Añade comentarios explicando las decisiones de diseño importantes.

## Conclusión

El sistema de migraciones de LightWeight proporciona una forma organizada de gestionar la estructura de tu base de datos. Al seguir las convenciones y mejores prácticas descritas en esta guía, puedes mantener un historial claro de cambios y facilitar el trabajo en equipo y los despliegues en diferentes entornos.
