# API de Referencia para Migraciones

## Clase Schema

La clase `Schema` proporciona una interfaz fluida para crear y modificar tablas de base de datos.

### Métodos Estáticos

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `Schema::create()` | Crea una nueva tabla | `(string $table, callable $callback)` |
| `Schema::table()` | Modifica una tabla existente | `(string $table, callable $callback)` |
| `Schema::drop()` | Elimina una tabla | `(string $table)` |
| `Schema::dropIfExists()` | Elimina una tabla si existe | `(string $table)` |
| `Schema::rename()` | Renombra una tabla existente | `(string $from, string $to)` |

### Ejemplos de Uso

```php
// Crear tabla
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
});

// Modificar tabla
Schema::table('productos', function (Blueprint $table) {
    $table->string('descripcion')->nullable();
});

// Renombrar tabla
Schema::rename('productos', 'articulos');

// Eliminar tabla
Schema::drop('productos');
```

## Clase Blueprint

La clase `Blueprint` se utiliza dentro de los callbacks de `Schema` para definir la estructura de las tablas.

### Métodos de Definición de Columnas

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `id()` | Añade ID auto-incremental | `(string $column = 'id')` |
| `string()` | Columna VARCHAR | `(string $column, int $length = 255)` |
| `integer()` | Columna INT | `(string $column)` |
| `bigInteger()` | Columna BIGINT | `(string $column)` |
| `smallInteger()` | Columna SMALLINT | `(string $column)` |
| `tinyInteger()` | Columna TINYINT | `(string $column)` |
| `unsignedInteger()` | INT sin signo | `(string $column)` |
| `unsignedBigInteger()` | BIGINT sin signo | `(string $column)` |
| `boolean()` | Columna BOOLEAN/TINYINT | `(string $column)` |
| `date()` | Columna DATE | `(string $column)` |
| `dateTime()` | Columna DATETIME | `(string $column)` |
| `time()` | Columna TIME | `(string $column)` |
| `timestamp()` | Columna TIMESTAMP | `(string $column)` |
| `timestamps()` | Columnas created_at y updated_at | `()` |
| `decimal()` | Columna DECIMAL | `(string $column, int $precision = 8, int $scale = 2)` |
| `float()` | Columna FLOAT | `(string $column, int $precision = 8, int $scale = 2)` |
| `text()` | Columna TEXT | `(string $column)` |
| `mediumText()` | Columna MEDIUMTEXT | `(string $column)` |
| `longText()` | Columna LONGTEXT | `(string $column)` |
| `binary()` | Columna BLOB | `(string $column)` |
| `json()` | Columna JSON | `(string $column)` |
| `enum()` | Columna ENUM | `(string $column, array $values)` |
| `year()` | Columna YEAR | `(string $column)` |

### Modificadores de Columnas

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `nullable()` | Permite NULL | `(bool $value = true)` |
| `default()` | Valor por defecto | `(mixed $value)` |
| `unsigned()` | Sin signo (para numéricos) | `()` |
| `unique()` | Índice único | `(string $indexName = null)` |
| `index()` | Índice normal | `(string $indexName = null)` |
| `primary()` | Clave primaria | `(string $indexName = null)` |
| `autoIncrement()` | Auto-incremental | `()` |
| `comment()` | Comentario | `(string $comment)` |
| `after()` | Posición después de otra columna | `(string $column)` |
| `first()` | Posición al principio | `()` |

### Métodos para Alteraciones

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `dropColumn()` | Elimina columna | `(string $column)` |
| `renameColumn()` | Renombra columna | `(string $from, string $to, ?string $type = null, array $options = [])` |
| `change()` | Modifica tipo de columna | `(string $column, string $type, array $parameters = [])` |
| `changeToNullable()` | Permite NULL en columna | `(string $column)` |
| `changeToNotNull()` | No permite NULL en columna | `(string $column)` |

### Métodos para Índices

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `index()` | Crea índice | `(string|array $columns, string $name = null)` |
| `unique()` | Crea índice único | `(string|array $columns, string $name = null)` |
| `primary()` | Establece clave primaria | `(string|array $columns, string $name = null)` |
| `dropIndex()` | Elimina índice | `(string $indexName)` |
| `dropUnique()` | Elimina índice único | `(string $indexName)` |
| `dropPrimary()` | Elimina clave primaria | `(string $indexName = null)` |
| `renameIndex()` | Renombra índice | `(string $from, string $to)` |

### Métodos para Claves Foráneas

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `foreign()` | Inicia definición de FK | `(string $foreignColumn)` |
| `references()` | Columna referenciada | `(string $column)` |
| `on()` | Tabla referenciada | `(string $table)` |
| `onDelete()` | Acción en eliminación | `(string $action)` |
| `onUpdate()` | Acción en actualización | `(string $action)` |
| `dropForeign()` | Elimina clave foránea | `(string $foreignKey)` |

#### Acciones Referenciales para Claves Foráneas

Las opciones disponibles para los métodos `onDelete()` y `onUpdate()` son:

| Acción | Descripción |
|--------|-------------|
| `CASCADE` | Al eliminar/actualizar el registro padre, los registros hijos relacionados se eliminan/actualizan automáticamente |
| `SET NULL` | Al eliminar/actualizar el registro padre, los valores de las claves foráneas se establecen a NULL (la columna debe ser nullable) |
| `RESTRICT` | Impide la eliminación/actualización del registro padre si existen registros hijos relacionados |
| `NO ACTION` | Similar a RESTRICT, pero la verificación se realiza después de intentar modificar todas las filas |
| `SET DEFAULT` | Al eliminar/actualizar el registro padre, las claves foráneas se establecen a su valor por defecto |

> **Importante**: Cuando se usan los métodos `onDelete()` y `onUpdate()`, estos deben llamarse **antes** que el método `on()`.

**Ejemplo correcto:**
```php
$table->foreign('user_id')
      ->references('id')
      ->onDelete('CASCADE')
      ->onUpdate('CASCADE')
      ->on('users');
```

**Ejemplo incorrecto:**
```php
$table->foreign('user_id')
      ->references('id')
      ->on('users')
      ->onDelete('CASCADE');  // Esto no tendrá efecto
```

### Configuración de Tabla

| Método | Descripción | Parámetros |
|--------|-------------|------------|
| `engine()` | Motor de almacenamiento | `(string $engine)` |
| `charset()` | Conjunto de caracteres | `(string $charset)` |
| `collation()` | Colación | `(string $collation)` |
| `temporary()` | Tabla temporal | `()` |

## Ejemplos de Uso Avanzado

### Crear Tabla con Múltiples Tipos de Columnas

```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 100);
    $table->string('apellido', 100);
    $table->string('email')->unique();
    $table->string('telefono', 20)->nullable();
    $table->text('direccion')->nullable();
    $table->enum('tipo', ['regular', 'premium', 'vip'])->default('regular');
    $table->decimal('saldo', 10, 2)->default(0.00);
    $table->integer('puntos')->unsigned()->default(0);
    $table->boolean('activo')->default(true);
    $table->date('fecha_nacimiento')->nullable();
    $table->timestamp('ultimo_login')->nullable();
    $table->timestamps();
    
    // Índices adicionales
    $table->index('apellido');
    $table->index(['tipo', 'activo']);
    
    // Configuración de tabla
    $table->engine('InnoDB');
    $table->charset('utf8mb4');
    $table->collation('utf8mb4_unicode_ci');
});
```

### Modificar Tabla con Operaciones Complejas

```php
Schema::table('clientes', function (Blueprint $table) {
    // Cambiar tipos de columnas
    $table->change('puntos', 'BIGINT', ['unsigned' => true]);
    
    // Renombrar columnas
    $table->renameColumn('nombre', 'primer_nombre');
    $table->renameColumn('apellido', 'apellido_paterno');
    
    // Añadir nuevas columnas en posiciones específicas
    $table->string('apellido_materno', 100)->nullable()->after('apellido_paterno');
    $table->string('nombre_completo')->after('primer_nombre');
    
    // Cambiar configuración de columnas existentes
    $table->string('telefono', 30)->nullable(false)->change();
    
    // Renombrar índices
    $table->renameIndex('clientes_apellido_index', 'idx_apellido_paterno');
    
    // Añadir una clave foránea
    $table->unsignedBigInteger('ciudad_id')->after('direccion');
    $table->foreign('ciudad_id')
          ->references('id')
          ->on('ciudades')
          ->onDelete('restrict')
          ->onUpdate('cascade');
});
```

## Notas Importantes

1. **Orden de eliminación**: Al eliminar tablas que tienen relaciones, asegúrate de eliminarlas en el orden correcto para evitar errores de integridad referencial.

2. **Renombrado de columnas**: Al renombrar una columna con `renameColumn()`, es recomendable especificar el tipo de la columna para mantener todas sus características.

3. **Modificación de columnas**: El método `change()` requiere que especifiques todos los atributos que deseas conservar, ya que reemplaza la definición completa.

4. **Compatibilidad**: Algunas operaciones como `after()` y `renameIndex()` son específicas de ciertos motores de base de datos (principalmente MySQL).

5. **Rendimiento**: Las operaciones de modificación de esquema pueden ser costosas en tablas grandes, considera ejecutarlas en momentos de baja carga o en ventanas de mantenimiento.
