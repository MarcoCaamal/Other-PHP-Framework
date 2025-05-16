# Documentación del Constructor de Esquemas

> 🌐 [English Documentation](../en/migrations-schema-builder.md)

El constructor de esquemas proporciona una interfaz fluida y conveniente para crear y modificar tablas de base de datos. Aquí mostramos cómo usarlo efectivamente:

## Creación de Tablas

Para crear una nueva tabla, usa el método `Schema::create`:

```php
Schema::create('nombre_tabla', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->integer('edad');
    $table->boolean('activo');
    $table->timestamps();
});
```

## Tipos de Columnas Disponibles

El objeto Blueprint proporciona una variedad de tipos de columnas que puedes usar:

- `$table->id()`: Columna ID con autoincremento
- `$table->bigInteger('columna')`: Columna BIGINT
- `$table->binary('columna')`: Columna BLOB
- `$table->boolean('columna')`: Columna TINYINT(1)
- `$table->date('columna')`: Columna DATE
- `$table->datetime('columna')`: Columna DATETIME
- `$table->decimal('columna', $precision, $escala)`: Columna DECIMAL con precisión y escala
- `$table->enum('columna', ['opcion1', 'opcion2'])`: Columna ENUM con opciones especificadas
- `$table->integer('columna')`: Columna INT
- `$table->json('columna')`: Columna JSON
- `$table->longText('columna')`: Columna LONGTEXT
- `$table->mediumInteger('columna')`: Columna MEDIUMINT
- `$table->mediumText('columna')`: Columna MEDIUMTEXT
- `$table->smallInteger('columna')`: Columna SMALLINT
- `$table->string('columna', $longitud)`: Columna VARCHAR con longitud opcional (predeterminada 255)
- `$table->text('columna')`: Columna TEXT
- `$table->time('columna')`: Columna TIME
- `$table->timestamp('columna')`: Columna TIMESTAMP
- `$table->tinyInteger('columna')`: Columna TINYINT
- `$table->unsignedBigInteger('columna')`: Columna UNSIGNED BIGINT
- `$table->unsignedInteger('columna')`: Columna UNSIGNED INT
- `$table->unsignedSmallInteger('columna')`: Columna UNSIGNED SMALLINT
- `$table->unsignedTinyInteger('columna')`: Columna UNSIGNED TINYINT
- `$table->year('columna')`: Columna YEAR

## Modificadores de Columnas

Puedes encadenar modificadores a tus definiciones de columnas:

```php
$table->string('email')->nullable()->unique();
```

Modificadores disponibles:

- `->autoIncrement()`: Establece la columna como autoincremental
- `->comment('Algún comentario')`: Añade un comentario a la columna
- `->default($valor)`: Establece un valor predeterminado para la columna
- `->nullable($valor = true)`: Configura la columna para permitir valores NULL
- `->unique()`: Añade una restricción única a la columna
- `->unsigned()`: Establece la columna como sin signo (para columnas de enteros)
- `->columnCharset('utf8mb4')`: Establece el juego de caracteres para la columna
- `->columnCollation('utf8mb4_unicode_ci')`: Establece la colación para la columna

## Índices

Puedes añadir índices a tus tablas:

```php
// Índice de columna única
$table->index('email');

// Índice compuesto
$table->index(['nombre', 'apellido'], 'indice_nombre');

// Índice único
$table->uniqueIndex('email');

// Clave primaria
$table->primary('id');
// O clave primaria compuesta
$table->primary(['id', 'tipo']);
```

## Claves Foráneas

Para añadir restricciones de clave foránea:

```php
$table->foreign('user_id')
      ->references('id')
      ->on('users');
```

## Operaciones de Tabla

Otras operaciones de tabla incluyen:

```php
// Eliminar tabla si existe
Schema::dropIfExists('usuarios');

// Modificar una tabla
Schema::table('usuarios', function (Blueprint $table) {
    $table->string('telefono')->nullable();
});
```

## Motor, Juego de Caracteres y Colación

Puedes establecer el motor, juego de caracteres y colación para tus tablas:

```php
$table->engine('InnoDB');
$table->charset('utf8mb4');
$table->collation('utf8mb4_unicode_ci');
```

## Eliminar Columnas e Índices

Para eliminar columnas:

```php
// Eliminar una sola columna
$table->dropColumn('columna');

// Eliminar múltiples columnas
$table->dropColumn(['columna1', 'columna2']);
```

Para eliminar índices:

```php
$table->dropIndex('nombre_indice');
$table->dropPrimary();
$table->dropUnique('nombre_indice_unico');
```

## Marcas de Tiempo (Timestamps)

Puedes añadir columnas created_at y updated_at de una sola vez:

```php
$table->timestamps();
```

Esto es equivalente a:

```php
$table->datetime('created_at');
$table->datetime('updated_at')->nullable();
```
