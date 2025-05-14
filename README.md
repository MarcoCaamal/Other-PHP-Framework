# LightWeight PHP Framework

LightWeight es un framework PHP ligero y elegante diseñado para facilitar el desarrollo de aplicaciones web modernas y APIs RESTful. Inspirado en las mejores prácticas actuales, ofrece una estructura clara y componentes desacoplados que favorecen el desarrollo rápido y mantenible.

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
- **CLI incorporada** - Comandos para tareas comunes de desarrollo

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

---

## Documentación completa

Para más información sobre cómo utilizar LightWeight, consulta la documentación disponible:

### Sistema de base de datos y migraciones
- [Guía de creación de migraciones](docs/create-migration-guide.md)
- [Referencia de API para migraciones](docs/migration-api-reference.md)
- [Mejoras del sistema de migraciones](docs/migration-system-enhancements.md)
- [Documentación de mejoras en migraciones](docs/migration-enhancement-documentation.md)
- [Mejores prácticas para migraciones](docs/migration-best-practices.md)
- [Transacciones de base de datos](docs/database-transactions.md)

### Componentes del framework
- [Guía de controladores](docs/controllers-guide.md)
- [Sistema de middleware](docs/middleware-guide.md)
- [Sistema de enrutamiento](docs/routing-guide.md)
- [Manejo de peticiones y respuestas](docs/request-response-handling.md)
- [Vistas y sistema de plantillas](docs/views-templating.md)
- [Sistema de autenticación](docs/authentication-guide.md)
- [Sistema de validación](docs/validation-guide.md)
