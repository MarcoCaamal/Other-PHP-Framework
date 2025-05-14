# Mejoras en el Sistema de Migraciones de LightWeight

## Introducción

Este documento describe las nuevas funcionalidades agregadas al sistema de migraciones del framework LightWeight para permitir operaciones avanzadas de manipulación de esquemas de base de datos, incluyendo el renombrado de tablas, columnas e índices, así como la modificación de tipos de columna.

## Nuevas Funcionalidades

### 1. Renombrar Tablas

Se ha añadido un nuevo método estático `Schema::rename()` que permite renombrar tablas existentes en la base de datos.

#### Sintaxis
```php
Schema::rename(string $from, string $to): void;
```

#### Parámetros
- `$from`: Nombre actual de la tabla
- `$to`: Nuevo nombre para la tabla

#### Ejemplo de uso
```php
Schema::rename('users', 'app_users');
```

### 2. Renombrar Columnas

Ahora es posible renombrar columnas existentes dentro de una tabla mediante el método `renameColumn()` en la clase `Blueprint`.

#### Sintaxis
```php
public function renameColumn(string $from, string $to, ?string $type = null, array $options = []): self;
```

#### Parámetros
- `$from`: Nombre actual de la columna
- `$to`: Nuevo nombre para la columna
- `$type`: (Opcional) Tipo de datos para la columna (VARCHAR, INT, etc.)
- `$options`: (Opcional) Opciones adicionales como longitud, precisión, etc.

#### Ejemplo de uso
```php
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('name', 'full_name');
    
    // Con tipo específico
    $table->renameColumn('description', 'bio', 'TEXT');
    
    // Con opciones
    $table->renameColumn('email', 'contact_email', 'VARCHAR', ['length' => 100]);
});
```

### 3. Modificar Columnas

Se ha implementado el método `change()` para modificar el tipo o propiedades de una columna existente.

#### Sintaxis
```php
public function change(string $column, string $type, array $parameters = []): self;
```

#### Parámetros
- `$column`: Nombre de la columna a modificar
- `$type`: Nuevo tipo para la columna
- `$parameters`: Parámetros adicionales para el tipo de columna

#### Ejemplo de uso
```php
Schema::table('products', function (Blueprint $table) {
    // Cambiar tipo de columna
    $table->change('price', 'DECIMAL', [
        'precision' => 10, 
        'scale' => 2
    ]);
    
    // Cambiar longitud de columna VARCHAR
    $table->change('name', 'VARCHAR', ['length' => 100]);
    
    // Cambiar tipo de columna a TEXT
    $table->change('description', 'TEXT');
    
    // Métodos auxiliares para nullable/not null
    $table->changeToNullable('optional_field');
    $table->changeToNotNull('required_field');
});
```

### 4. Renombrar Índices

El método `renameIndex()` permite renombrar índices existentes en una tabla.

#### Sintaxis
```php
public function renameIndex(string $from, string $to): self;
```

#### Parámetros
- `$from`: Nombre actual del índice
- `$to`: Nuevo nombre para el índice

#### Ejemplo de uso
```php
Schema::table('posts', function (Blueprint $table) {
    $table->renameIndex('posts_user_id_index', 'posts_author_id_index');
});
```

## Compatibilidad con MySQL

Estas operaciones son compatibles con MySQL y siguen las mejores prácticas de manipulación de esquemas. Las operaciones de renombrado y modificación preservan los datos existentes en las tablas.

## Notas Importantes

- Al renombrar columnas, es necesario especificar el tipo de datos si se quiere mantener el mismo tipo.
- Las operaciones de modificación de columnas pueden llevar tiempo en tablas grandes y deben realizarse con precaución.
- Se recomienda hacer respaldos antes de realizar operaciones de esquema en entornos de producción.
- Estas funcionalidades son útiles para refactorizaciones y evolución de esquemas sin necesidad de recrear tablas.
    $table->renameColumn('nombre', 'name');
    
    // Especificando el tipo
    $table->renameColumn('descripcion', 'description', 'TEXT');
    
    // Con opciones adicionales
    $table->renameColumn('email', 'contact_email', 'VARCHAR', [
        'length' => 100
    ]);
});
```

### 3. Modificar Columnas

Ahora puedes cambiar el tipo y propiedades de columnas existentes:

```php
<?php

use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;

Schema::table('productos', function (Blueprint $table) {
    // Cambiar tipo de columna con configuración específica
    $table->change('precio', 'DECIMAL', [
        'precision' => 10,
        'scale' => 2
    ]);
    
    // Cambiar VARCHAR con longitud personalizada
    $table->change('nombre', 'VARCHAR', [
        'length' => 100
    ]);
    
    // Para columnas ENUM
    $table->change('estado', 'ENUM', [
        'values' => ['activo', 'inactivo', 'pendiente']
    ]);
    
    // Métodos de ayuda para nullable
    $table->changeToNullable('descripcion');
    $table->changeToNotNull('codigo');
});
```

### 4. Renombrar Índices

También puedes renombrar índices existentes:

```php
<?php

use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;

Schema::table('articulos', function (Blueprint $table) {
    $table->renameIndex('articulos_categoria_id_index', 'articulos_category_id_index');
});
```

## Ejemplos de Uso

### Migración Completa con Todas las Características

```php
<?php

use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Migrations\Blueprint;

class UpdateUserTableStructure extends Migration
{
    public function up()
    {
        // 1. Renombrar tabla
        Schema::rename('usuarios', 'users');
        
        // 2. Modificar estructura
        Schema::table('users', function (Blueprint $table) {
            // Renombrar columnas
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('apellido', 'last_name');
            
            // Modificar tipos
            $table->change('bio', 'TEXT');
            $table->change('points', 'INT');
            
            // Hacer nullable algunas columnas
            $table->changeToNullable('website');
            
            // Renombrar índices
            $table->renameIndex('usuarios_email_unique', 'users_email_unique');
        });
    }
    
    public function down()
    {
        // Revertir los cambios en orden inverso
        Schema::table('users', function (Blueprint $table) {
            // Revertir índices
            $table->renameIndex('users_email_unique', 'usuarios_email_unique');
            
            // Revertir columnas nullable
            $table->changeToNotNull('website');
            
            // Revertir tipos
            $table->change('points', 'DECIMAL', ['precision' => 8, 'scale' => 2]);
            $table->change('bio', 'VARCHAR', ['length' => 255]);
            
            // Revertir nombres de columnas
            $table->renameColumn('last_name', 'apellido');
            $table->renameColumn('name', 'nombre');
        });
        
        // Revertir nombre de tabla
        Schema::rename('users', 'usuarios');
    }
}
```

## Consideraciones

- Al renombrar columnas, es recomendable especificar el tipo para mantener la consistencia.
- Al cambiar tipos de columnas, considera si hay datos que podrían perderse durante la conversión.
- Los métodos de cambio pueden combinarse con métodos de adición y eliminación en la misma migración.
- Siempre crea el método `down()` para poder revertir los cambios si es necesario.
