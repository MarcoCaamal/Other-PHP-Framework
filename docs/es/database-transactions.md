# Transacciones en Base de Datos

## Introducción

Las transacciones son un concepto fundamental en el manejo de bases de datos que permiten agrupar múltiples operaciones para que se ejecuten como una sola unidad atómica. Esto garantiza que, o bien todas las operaciones se completen con éxito, o bien ninguna de ellas tenga efecto en caso de error. LightWeight Framework provee una API simple para trabajar con transacciones.

## Conceptos Básicos

Una transacción tiene cuatro propiedades fundamentales conocidas como ACID:

1. **Atomicidad**: Todas las operaciones se ejecutan o ninguna se ejecuta.
2. **Consistencia**: La base de datos pasa de un estado válido a otro estado válido.
3. **Aislamiento**: Los efectos de una transacción no son visibles para otras transacciones hasta que se confirma.
4. **Durabilidad**: Una vez confirmada, una transacción persiste incluso en caso de fallos del sistema.

## API de Transacciones

LightWeight ofrece una interfaz sencilla para trabajar con transacciones a través de la clase `DB`.

### Transacciones Básicas

```php
use LightWeight\Database\DB;

// Iniciar una transacción
DB::beginTransaction();

try {
    // Realizar operaciones de base de datos
    DB::table('cuentas')->where('id', 1)->update(['balance' => DB::raw('balance - 100')]);
    DB::table('cuentas')->where('id', 2)->update(['balance' => DB::raw('balance + 100')]);
    
    // Confirmar la transacción
    DB::commit();
} catch (\Exception $e) {
    // Si ocurre cualquier error, revertir la transacción
    DB::rollback();
    
    // Manejar la excepción
    throw $e;
}
```

## Casos de Uso Comunes

### Transferencias Financieras

Un caso de uso clásico para transacciones es la transferencia de fondos entre cuentas:

```php
// Iniciar transacción
DB::beginTransaction();

try {
    // Verificar fondos suficientes
    $cuentaOrigen = DB::table('cuentas')->where('id', $cuentaOrigenId)->first();
    
    if ($cuentaOrigen['balance'] < $monto) {
        throw new \Exception('Fondos insuficientes');
    }
    
    // Retirar de cuenta origen
    DB::table('cuentas')
        ->where('id', $cuentaOrigenId)
        ->update(['balance' => DB::raw("balance - $monto")]);
    
    // Depositar en cuenta destino
    DB::table('cuentas')
        ->where('id', $cuentaDestinoId)
        ->update(['balance' => DB::raw("balance + $monto")]);
    
    // Registrar la transacción
    DB::table('movimientos')->insert([
        'cuenta_origen' => $cuentaOrigenId,
        'cuenta_destino' => $cuentaDestinoId,
        'monto' => $monto,
        'fecha' => date('Y-m-d H:i:s'),
        'concepto' => 'Transferencia'
    ]);
    
    // Confirmar transacción
    DB::commit();
} catch (\Exception $e) {
    // Revertir transacción en caso de error
    DB::rollback();
    throw $e;
}
```

### Registro de Usuario con Perfil

Otro caso común es la creación de un usuario junto con su perfil asociado:

```php
DB::beginTransaction();

try {
    // Crear usuario
    DB::table('usuarios')->insert([
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
        'creado_en' => date('Y-m-d H:i:s')
    ]);
    
    $userId = DB::lastInsertId();
    
    // Crear perfil relacionado
    DB::table('perfiles')->insert([
        'usuario_id' => $userId,
        'biografia' => $datos['biografia'] ?? null,
        'website' => $datos['website'] ?? null,
        'avatar' => 'default.png'
    ]);
    
    // Asignar roles por defecto
    DB::table('usuario_roles')->insert([
        'usuario_id' => $userId,
        'rol_id' => 2 // Rol "usuario" predeterminado
    ]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

## Mejores Prácticas

### 1. Mantén las Transacciones Cortas

Las transacciones mantienen bloqueos en la base de datos, lo que puede impactar el rendimiento. Intenta que sean lo más breves posible.

### 2. Captura Excepciones Específicas

Captura tipos específicos de excepciones para manejar diferentes casos de error:

```php
try {
    DB::beginTransaction();
    
    // Código que podría fallar
    
    DB::commit();
} catch (\PDOException $e) {
    DB::rollback();
    // Manejar error de base de datos
} catch (\Exception $e) {
    DB::rollback();
    // Manejar otros errores
}
```

### 3. Evita Operaciones Externas

No realices operaciones externas (como llamadas API o tareas de larga duración) dentro de una transacción:

```php
// Mal: Operación externa dentro de transacción
DB::beginTransaction();
try {
    DB::table('pedidos')->insert(['cliente_id' => 1]);
    $resultado = ServicioExterno::verificarInventario(); // ¡Mala práctica!
    DB::table('inventario')->update(['stock' => DB::raw('stock - 1')]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}

// Bien: Operación externa fuera de la transacción
$resultado = ServicioExterno::verificarInventario();
if ($resultado->tieneStock) {
    DB::beginTransaction();
    try {
        DB::table('pedidos')->insert(['cliente_id' => 1]);
        DB::table('inventario')->update(['stock' => DB::raw('stock - 1')]);
        DB::commit();
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 4. Usa Niveles de Aislamiento Apropiados

Los niveles de aislamiento determinan cómo se comportan múltiples transacciones concurrentes. Puedes configurar esto usando sentencias SQL directas:

```php
// Establecer nivel de aislamiento para una transacción específica
DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
DB::beginTransaction();
// Operaciones de transacción
DB::commit();
```

## Niveles de Aislamiento

MySQL soporta cuatro niveles de aislamiento:

1. **READ UNCOMMITTED**: Puede leer datos no confirmados (dirty reads).
2. **READ COMMITTED**: Solo lee datos confirmados.
3. **REPEATABLE READ**: Garantiza lecturas consistentes dentro de la transacción (predeterminado en InnoDB).
4. **SERIALIZABLE**: El nivel más estricto, previene anomalías de concurrencia.

## Conclusión

Las transacciones son esenciales para mantener la integridad de los datos en aplicaciones que realizan múltiples operaciones relacionadas en la base de datos. LightWeight ofrece una API simple pero efectiva para trabajar con transacciones. Usándolas correctamente, puedes asegurar que tu aplicación mantenga un estado coherente incluso en situaciones de error.

> 🌐 [English Documentation](../en/database-transactions.md)
