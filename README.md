# LightWeight PHP Framework

LightWeight is a lightweight and elegant PHP framework designed to facilitate the development of modern web applications and RESTful APIs. Inspired by current best practices, it offers a clear structure and decoupled components that promote rapid and maintainable development.

> 🌐 [Documentación en Español](README.es.md)

## New Features (May 2025)

- **Enhanced Controller Generation** - Improved `make:controller` command with better route file handling, support for custom route filenames, and more intelligent handling of different controller types.
- **Event System (Observer Pattern)** - Implementation of the observer pattern to manage events in the application, allowing components to be decoupled and improving extensibility.
- **Support for Foreign Key Actions** - Now you can specify `ON DELETE` and `ON UPDATE` behaviors (CASCADE, SET NULL, etc.) in foreign key relationships.
- **Improved Constraint Naming System** - Better handling of constraint names with support to avoid collisions even with referential actions.
- **Integrated Validation of Referential Actions** - Automatic validation of allowed actions to maintain database integrity.
- **Advanced Exception Handling System** - Centralized exception handling with support for customization, logging, and automatic notifications for critical errors.
- **Email System** - Complete email system with support for templates and multiple drivers.

## Main Features

- **MVC Architecture** - Clear organization following the Model-View-Controller pattern
- **ORM System** - Database interaction through models that follow the active record pattern
- **Dependency Container** - Dependency injection for decoupled code
- **Intuitive Routing** - Flexible system for defining application routes
- **Database Migrations** - Version control for data schema
- **Robust Validation** - Validation of input data with customizable rules
- **Authentication and Authorization** - Secure system with JWT support
- **Template System** - Lightweight and powerful view engine
- **Session Management** - Simple handling of session data
- **Event System** - Implementation of the observer pattern for decoupled communication between components
- **Built-in CLI** - Commands for common development tasks
- **Centralized Exception Handling** - Modular system for managing, reporting, and displaying errors
- **Critical Error Notification** - Automatic alerts via email, Slack, or other channels

## Documentation

The complete framework documentation is available in the `/docs` folder. You can start at the [documentation index](docs/index.md) which contains links to all available guides, including:

- Basic architecture (routes, controllers, middleware, views)
- Main features (requests/responses, authentication, validation, events, emails)
- Database (migrations, transactions, schema, foreign keys)
- Recent updates

## Requirements

- PHP 8.4 or higher
- Composer
- MySQL/MariaDB (for database features)

## Installation

```bash
composer require marco/lightweight
```

Or clone the repository to start a project:

```bash
git clone https://github.com/yourusername/lightweight.git
cd lightweight
composer install
```

## Basic Usage

### Creating a Route

```php
<?php
// routes.php

use LightWeight\Routing\Route;

Route::get('/', function() {
    return view('welcome');
});

Route::get('/users/{id}', 'UserController@show');
```

### Creating a Controller

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
            return Response::notFound('User not found');
        }
        
        return Response::json($user);
    }
}
```

### Using the Event System

```php
<?php
// Register a listener for an event
on('user.created', function($event) {
    $user = $event->getData()['user'];
    // Send welcome email
    mailTemplate($user->email, 'Welcome to our app', 'welcome', ['userName' => $user->name]);
});

// Trigger the event when a user is created
$user = new User();
$user->fill($request->all());

if ($user->save()) {
    // Notify other system components
    event('user.created', ['user' => $user]);
    return redirect('/dashboard');
}
```

For a complete guide on the event system, see the [events documentation](docs/en/events-guide.md).

### Defining a Model

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

### Using Migrations

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

### Sending Emails

```php
<?php
// Send a simple email
mailSend(
    'recipient@example.com',
    'Hello from LightWeight',
    '<p>This is a <strong>test email</strong> from LightWeight framework!</p>'
);

// Send an email using a template
mailTemplate(
    'new-user@example.com',
    'Welcome to our platform',
    'welcome',  // Using resources/views/emails/welcome.php template
    [
        'userName' => 'John Doe',
        'activationLink' => 'https://example.com/activate/123'
    ]
);
```

For a complete guide on the email system, see the [email system documentation](docs/en/mail-system.md).

## CLI Commands

LightWeight includes a command interface for common tasks:

```bash
# Create a new controller (with various options)
php light make:controller UserController --type=web --routes --views --all

# Create a controller with a custom route file
php light make:controller ProductController --type=api --routes=custom

# Create a model
php light make:model User

# Create a migration
php light make:migration create_users_table

# Run migrations
php light migrate

# View available routes
php light routes:list
```

For detailed information about the controller command, see the [make:controller documentation](docs/en/make-controller-command.md).

## Troubleshooting

### View Rendering Problems

If you see errors like `View file not found: errors/404.php`, you may need to verify:

1. That view folders exist in the expected locations
2. That view paths are properly configured

Possible solutions:
- Ensure your `.env` file has the correct path settings
- Check your `config/view.php` configuration file for correct paths
- Use the `setViewsDirectory()` method to change view location at runtime if needed

## Testing

LightWeight has integration with PHPUnit for unit and integration tests:

```bash
composer run tests
```

## Contributing

Contributions are welcome and appreciated. Please read the contribution guidelines before submitting a pull request.

## License

LightWeight is open-source software licensed under the MIT license.

## Credits

Developed by Marco.
