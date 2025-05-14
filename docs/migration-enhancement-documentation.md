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
});
```

## Compatibilidad con MySQL

Estas operaciones son compatibles con MySQL y siguen las mejores prácticas de manipulación de esquemas. Las operaciones de renombrado y modificación preservan los datos existentes en las tablas.

## Notas Importantes

- Al renombrar columnas, es necesario especificar el tipo de datos si se quiere mantener el mismo tipo.
- Las operaciones de modificación de columnas pueden llevar tiempo en tablas grandes y deben realizarse con precaución.
- Se recomienda hacer respaldos antes de realizar operaciones de esquema en entornos de producción.
- Estas funcionalidades son útiles para refactorizaciones y evolución de esquemas sin necesidad de recrear tablas.
