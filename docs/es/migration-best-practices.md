# Mejores Prácticas para Migraciones en LightWeight

> 🌐 [English Documentation](../en/migration-best-practices.md)

## Diseño y Organización

### Atomicidad de las Migraciones

Cada migración debe realizar un conjunto lógico y atómico de cambios en la estructura de la base de datos. Esto hace que las migraciones sean más fáciles de entender, probar y revertir.

**Recomendado:**
- Una migración para crear una tabla y sus índices básicos
- Una migración separada para añadir relaciones entre tablas
- Una migración específica para modificar columnas existentes

**No recomendado:**
- Modificar varias tablas no relacionadas en una sola migración
- Mezclar cambios de estructura con inserción de datos

### Nomenclatura Consistente

Adopta un sistema de nomenclatura coherente para tus migraciones, tablas, columnas e índices:

**Para archivos de migración:**
```
YYYYMMDDHHMMSS_crear_tabla_usuarios.php
YYYYMMDDHHMMSS_agregar_columna_direccion_a_usuarios.php
YYYYMMDDHHMMSS_crear_relacion_usuarios_pedidos.php
```

**Para tablas:**
- Usa sustantivos en plural: `usuarios`, `productos`, `categorias`
- Para tablas pivote, usa nombres en singular separados por guion bajo: `producto_categoria`

**Para columnas:**
- Usa snake_case para nombres de columnas: `fecha_registro`, `precio_unitario`
- Usa `id` para claves primarias
- Usa `{tabla_singular}_id` para claves foráneas: `usuario_id`, `producto_id`

**Para índices:**
- Índices simples: `{tabla}_{columna}_index`
- Índices únicos: `{tabla}_{columna}_unique`
- Índices compuestos: `{tabla}_{columna1}_{columna2}_index`

## Implementación

### Siempre Define el Método Down()

Cada migración debe incluir un método `down()` que revierta exactamente lo que hace el método `up()`. Esto asegura que puedas retroceder y avanzar en el historial de migraciones sin problemas.

```php
public function up()
{
    Schema::table('usuarios', function (Blueprint $table) {
        $table->string('apellido', 100)->after('nombre');
    });
}

public function down()
{
    Schema::table('usuarios', function (Blueprint $table) {
        $table->dropColumn('apellido');
    });
}
```

### Considera el Rendimiento

Las operaciones de esquema pueden ser costosas, especialmente en tablas grandes:

1. **Agrupa modificaciones**: Combina múltiples alteraciones de una misma tabla en una sola llamada a `Schema::table()`.

2. **Ten cuidado con los índices en tablas grandes**: Añadir un índice a una tabla con millones de filas puede bloquear la tabla durante el tiempo de creación.

3. **Considera el uso de migraciones sin bloqueo**: Para bases de datos en producción, investiga técnicas para modificar esquemas sin bloquear las tablas.

```php
// Mejor: Agrupar modificaciones de una misma tabla
Schema::table('productos', function (Blueprint $table) {
    $table->string('sku')->after('nombre');
    $table->decimal('impuesto', 5, 2)->after('precio');
    $table->boolean('destacado')->default(false);
});

// En lugar de tres operaciones separadas
```

### Gestiona las Relaciones en el Orden Correcto

Cuando creas o eliminas relaciones entre tablas:

1. **Creación**: Primero crea las tablas principales, luego las tablas dependientes, y finalmente añade las claves foráneas.
2. **Eliminación**: Primero elimina las claves foráneas, luego las tablas dependientes, y finalmente las tablas principales.

```php
// En el método up()
Schema::create('categorias', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->timestamps();
});

Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->unsignedBigInteger('categoria_id');
    $table->timestamps();
    
    $table->foreign('categoria_id')->references('id')->on('categorias');
});

// En el método down()
Schema::dropIfExists('productos'); // Primero la tabla con la clave foránea
Schema::dropIfExists('categorias'); // Después la tabla referenciada
```

### Define Tipos de Columnas Precisos

Usa el tipo de columna más adecuado para cada caso para optimizar el rendimiento y el almacenamiento:

```php
// Tipos específicos según el uso
$table->string('codigo_postal', 10); // Longitud limitada
$table->tinyInteger('edad')->unsigned(); // Rango 0-255 es suficiente
$table->decimal('precio', 10, 2); // Precisión monetaria
$table->enum('estado', ['pendiente', 'procesando', 'completado', 'cancelado']);
$table->text('descripcion'); // Para texto largo sin límite específico
```

### Usa Valores Predeterminados y Restricciones NOT NULL

Define claramente las restricciones de tus columnas:

```php
// Con restricciones bien definidas
$table->string('nombre')->nullable(false);
$table->boolean('activo')->default(true);
$table->dateTime('fecha_registro')->useCurrent();
$table->integer('intentos')->default(0);
```

## Gestión y Despliegue

### Nunca Modifiques una Migración Publicada

Una vez que una migración se ha aplicado en cualquier entorno (especialmente en producción), nunca debes modificar su contenido. En su lugar, crea una nueva migración que realice los cambios adicionales.

**Incorrecto:**
- Modificar el archivo `20220101000000_crear_tabla_usuarios.php` después de haberlo desplegado

**Correcto:**
- Crear una nueva migración `20220201000000_modificar_columna_email_en_usuarios.php`

### Prueba tus Migraciones

Antes de desplegar migraciones a producción:

1. Prueba la aplicación de la migración (`up()`)
2. Prueba la reversión de la migración (`down()`)
3. Prueba volver a aplicar la migración después de revertirla
4. Verifica la integridad de los datos después de estos ciclos

### Migración Segura en Producción

Para migraciones en entornos de producción:

1. **Realiza un respaldo** completo de la base de datos antes de aplicar migraciones
2. **Programa las migraciones** durante periodos de baja actividad
3. **Planifica el tiempo de inactividad** necesario para migraciones complejas
4. **Ten un plan de rollback** en caso de problemas

## Casos de Uso Específicos

### Migraciones para Modificar Datos

Aunque las migraciones se usan principalmente para cambios de esquema, también puedes usarlas para modificar datos:

```php
public function up()
{
    // Modificación de datos existentes
    DB::table('productos')->where('categoria', 'electronica')
        ->update(['departamento_id' => 5]);
}

public function down()
{
    // Revertir la modificación
    DB::table('productos')->where('departamento_id', 5)
        ->update(['categoria' => 'electronica', 'departamento_id' => null]);
}
```

### Migraciones Condicionales

En algunos casos, puedes necesitar que una migración se comporte de manera diferente según ciertas condiciones:

```php
public function up()
{
    if (!Schema::hasColumn('usuarios', 'apellido')) {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('apellido')->after('nombre')->nullable();
        });
    }
}
```

### Trabajando con Columnas JSON

Para bases de datos que admiten JSON:

```php
Schema::create('configuraciones', function (Blueprint $table) {
    $table->id();
    $table->string('clave')->unique();
    $table->json('valor');
    $table->timestamps();
});
```

## Seguridad y Validación

### Validación de Nombres

Valida siempre los nombres de tablas y columnas para evitar problemas de SQL injection:

```php
// Validar nombres de tablas
if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
    throw new InvalidArgumentException("Nombre de tabla inválido: $tableName");
}

// Nunca uses nombres dinámicos sin validación
$columnName = $request->input('column'); // Peligroso sin validación
```

### Protección de Datos Sensibles

Ten especial cuidado con columnas que almacenan datos sensibles:

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->string('password'); // Se almacenará encriptada, no en texto plano
    $table->string('api_key')->nullable(); // Dato sensible
    $table->text('perfil_json')->nullable();
    $table->timestamps();
    
    // Añade comentarios para datos sensibles
    $table->comment = 'Contiene datos personales sujetos a regulaciones de privacidad';
});
```

## Conclusión

Las migraciones son una herramienta poderosa para mantener la evolución de tu esquema de base de datos de forma controlada y reproducible. Siguiendo estas mejores prácticas, puedes crear un sistema robusto y mantenible que facilite el trabajo en equipo y los despliegues confiables en todos tus entornos.
