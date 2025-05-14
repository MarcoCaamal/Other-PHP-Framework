# Mejoras en el Sistema de Migraciones

## Nuevas características

### 1. Renombrar Tablas
```php
Schema::rename('old_table_name', 'new_table_name');
```

### 2. Renombrar Columnas
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
```php
Schema::table('products', function (Blueprint $table) {
    // Cambiar tipo de columna con parámetros
    $table->change('price', 'DECIMAL', [
        'precision' => 10, 
        'scale' => 2
    ]);
    
    // Cambiar el tipo a VARCHAR con longitud específica
    $table->change('code', 'VARCHAR', [
        'length' => 50
    ]);
    
    // Cambiar el tipo a ENUM con valores permitidos
    $table->change('status', 'ENUM', [
        'values' => ['pending', 'approved', 'rejected']
    ]);
    
    // Cambiar a nullable/not null
    $table->changeToNullable('description');
    $table->changeToNotNull('name');
});
```

### 4. Renombrar Índices
```php
Schema::table('posts', function (Blueprint $table) {
    $table->renameIndex('posts_user_id_index', 'posts_author_id_index');
});
```
```
