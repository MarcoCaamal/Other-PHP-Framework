# LightWeight PHP Framework

LightWeight es un framework PHP ligero y elegante diseñado para facilitar el desarrollo de aplicaciones web modernas y APIs RESTful. Inspirado en las mejores prácticas actuales, ofrece una estructura clara y componentes desacoplados que favorecen el desarrollo rápido y mantenible.

> 🌐 [English Documentation](README.md)

## Nuevas funcionalidades (Mayo 2025)

- **Sistema de eventos (Observer Pattern)** - Implementación del patrón observador para gestionar eventos en la aplicación, permitiendo desacoplar componentes y mejorar la extensibilidad.
- **Soporte para acciones en claves foráneas** - Ahora puedes especificar comportamientos `ON DELETE` y `ON UPDATE` (CASCADE, SET NULL, etc.) en las relaciones de clave foránea.
- **Sistema mejorado de nombres de restricciones** - Mejor manejo de nombres de restricciones con soporte para evitar colisiones incluso con acciones referenciales.
- **Validación integrada de acciones referenciales** - Validación automática de las acciones permitidas para mantener la integridad de la base de datos.
- **Sistema avanzado de manejo de excepciones** - Manejo centralizado de excepciones con soporte para personalización, logging y notificaciones automáticas para errores críticos.
- **Sistema de correo electrónico** - Sistema completo de correo con soporte para plantillas y múltiples drivers.

## Características principales

- **Arquitectura MVC** - Organización clara siguiendo el patrón Modelo-Vista-Controlador
- **Sistema ORM** - Interacción con la base de datos a través de modelos que sigue el patrón active record
- **Contenedor de dependencias** - Inyección de dependencias para código desacoplado
- **Enrutamiento intuitivo** - Sistema flexible para definir rutas de la aplicación
- **Migraciones de base de datos** - Control de versiones para el esquema de datos
- **Validación robusta** - Validación de datos de entrada con reglas personalizables
- **Autenticación y autorización** - Sistema seguro con soporte para JWT
- **Sistema de plantillas** - Motor de vistas ligero y potente
- **Gestión de sesiones** - Manejo sencillo de datos de sesión
- **Sistema de eventos** - Implementación del patrón observador para la comunicación desacoplada entre componentes
- **CLI incorporada** - Comandos para tareas comunes de desarrollo
- **Manejo de excepciones centralizado** - Sistema modular para gestionar, reportar y mostrar errores
- **Notificación de errores críticos** - Alertas automáticas por email, Slack u otros canales

## Documentación

La documentación completa del framework está disponible en la carpeta `/docs`. Puedes comenzar en el [índice de documentación](docs/index.md) que contiene enlaces a todas las guías disponibles, incluyendo:

- Arquitectura básica (rutas, controladores, middleware, vistas)
- Características principales (peticiones/respuestas, autenticación, validación, eventos, correos)
- Base de datos (migraciones, transacciones, esquema, claves foráneas)
- Actualizaciones recientes

## Requisitos

- PHP 8.4 o superior
- Composer
- MySQL/MariaDB (para características de base de datos)

## Instalación

```bash
composer require marco/lightweight
```

O clonar el repositorio para comenzar un proyecto:

```bash
git clone https://github.com/yourusername/lightweight.git
cd lightweight
composer install
```

## Uso básico

### Creación de una ruta

```php
<?php
// routes.php

use LightWeight\Routing\Route;

Route::get('/', function() {
    return view('welcome');
});

Route::get('/users/{id}', 'UserController@show');
```

### Creación de un controlador

```php
<?php
// app/Controllers/UserController.php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class UserController extends ControllerBase
{
    public function show(Request $request, $id)
    {
        $user = \App\Models\User::find($id);
        
        if (!$user) {
            return Response::notFound('Usuario no encontrado');
        }
        
        return Response::json($user);
    }
}
```

### Uso del sistema de eventos

```php
<?php
// Registrar un listener para un evento
on('user.created', function($event) {
    $user = $event->getData()['user'];
    // Enviar email de bienvenida
    mailTemplate($user->email, 'Bienvenido a nuestra aplicación', 'welcome', ['userName' => $user->name]);
});

// Disparar el evento cuando se crea un usuario
$user = new User();
$user->fill($request->all());

if ($user->save()) {
    // Notificar a otros componentes del sistema
    event('user.created', ['user' => $user]);
    return redirect('/dashboard');
}
```

Para una guía completa sobre el sistema de eventos, consulta la [documentación de eventos](docs/es/events-guide.md).

### Definición de un modelo

```php
<?php
// app/Models/User.php

namespace App\Models;

use LightWeight\Database\ORM\Model;

class User extends Model
{
    protected ?string $table = 'users';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

### Uso de migraciones

```php
<?php
// database/migrations/CreateUsersTable.php

use LightWeight\Database\Migrations\Migration;
use LightWeight\Database\QueryBuilder\Builder;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->schema->create('users', function(Builder $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('users');
    }
}
```

### Envío de correos electrónicos

```php
<?php
// Enviar un correo simple
mailSend(
    'destinatario@ejemplo.com',
    'Hola desde LightWeight',
    '<p>Este es un <strong>correo de prueba</strong> desde el framework LightWeight!</p>'
);

// Enviar un correo usando una plantilla
mailTemplate(
    'nuevo-usuario@ejemplo.com',
    'Bienvenido a nuestra plataforma',
    'welcome',  // Usando la plantilla resources/views/emails/welcome.php
    [
        'userName' => 'Juan Pérez',
        'activationLink' => 'https://ejemplo.com/activar/123'
    ]
);
```

Para una guía completa sobre el sistema de correos, consulta la [documentación del sistema de correo](docs/es/mail-system.md).

## Comandos CLI

LightWeight incluye una interfaz de comandos para tareas comunes:

```bash
# Crear un nuevo controlador
php light make:controller UserController

# Crear un nuevo modelo
php light make:model User

# Crear una migración
php light make:migration create_users_table

# Ejecutar migraciones
php light migrate

# Ver rutas disponibles
php light routes:list
```

## Testing

LightWeight tiene integración con PHPUnit para pruebas unitarias y de integración:

```bash
composer run tests
```

## Contribuir

Las contribuciones son bienvenidas y valoradas. Por favor, lee las directrices de contribución antes de enviar un pull request.

## Licencia

LightWeight es un software de código abierto bajo la licencia MIT.

## Créditos

Desarrollado por Marco.
