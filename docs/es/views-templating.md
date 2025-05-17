# Sistema de Vistas y Plantillas en LightWeight

## Introducción

El sistema de vistas y plantillas de LightWeight permite separar la lógica de presentación de tu aplicación de la lógica de negocio. LightWeight utiliza el motor de plantillas "LightEngine", una implementación ligera y eficiente diseñada específicamente para este framework, que ofrece un equilibrio perfecto entre simplicidad y potencia.

## Sistema de Respaldo de Plantillas

LightWeight incluye un sistema de respaldo de plantillas que permite al framework buscar automáticamente vistas en múltiples ubicaciones. Cuando solicitas una vista, el motor:

1. Primero verifica el directorio de vistas configurado (típicamente `resources/views`)
2. Si la vista no se encuentra, buscará en el directorio de plantillas predeterminado (`/templates/default/views`)

Este mecanismo de respaldo se aplica tanto a vistas estándar como a layouts, asegurando que tu aplicación siempre tenga acceso a plantillas esenciales para páginas de error, pantallas de bienvenida y otros componentes comunes.

El sistema de respaldo es especialmente útil para:
- Mostrar páginas de error pulidas sin tener que crearlas tú mismo
- Proporcionar una página de bienvenida para nuevos proyectos
- Asegurar que los mensajes y notificaciones del sistema tengan un estilo consistente

## Conceptos Básicos

### Estructura de Directorios

Las vistas en LightWeight normalmente se organizan en el directorio `resources/views`. Dentro de este directorio, puedes organizar tus vistas en subdirectorios para mantener una estructura clara:

```
resources/
  views/
    layouts/
      main.php
    partials/
      header.php
      footer.php
    users/
      index.php
      show.php
      edit.php
    errors/
      404.php
      500.php
```

Adicionalmente, el framework incluye plantillas predeterminadas en la siguiente ubicación:

```
templates/
  default/
    views/
      layouts/
        main.php
      errors/
        404.php
        500.php
      welcome.php
```

Estas plantillas predeterminadas sirven como respaldo cuando tu aplicación no define vistas específicas.

### Renderización Básica de Vistas

Para renderizar una vista desde un controlador:

```php
public function index()
{
    $users = User::all();
    return view('users.index', ['users' => $users]);
}
```

La función `view()` toma como primer parámetro la ruta relativa a la vista (usando punto como separador) y como segundo parámetro opcional un array de datos para pasar a la vista.

### Estructura de un Archivo de Vista

Los archivos de vista en LightWeight tienen la extensión `.light.php` y son básicamente archivos PHP con sintaxis adicional específica de LightEngine:

```php
<!-- resources/views/users/index.light.php -->
@extends('layouts.main')

@section('title', 'Lista de Usuarios')

@section('content')
    <h1>Usuarios</h1>
    
    <ul class="user-list">
        @foreach($users as $user)
            <li>
                <a href="{{ route('users.show', ['id' => $user->id]) }}">
                    {{ $user->name }}
                </a>
            </li>
        @endforeach
    </ul>
    
    @if(count($users) === 0)
        <p>No hay usuarios registrados.</p>
    @endif
@endsection
```

## Plantillas y Layouts

### Definición de un Layout

Un layout es una plantilla maestra que define la estructura general de tus páginas:

```php
<!-- resources/views/layouts/main.light.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi Aplicación')</title>
    <link rel="stylesheet" href="/css/app.css">
    @yield('styles')
</head>
<body>
    <header>
        @include('partials.header')
    </header>
    
    <main class="container">
        @yield('content')
    </main>
    
    <footer>
        @include('partials.footer')
    </footer>
    
    <script src="/js/app.js"></script>
    @yield('scripts')
</body>
</html>
```

### Extendiendo Layouts

Para utilizar un layout en una vista, usa la directiva `@extends`:

```php
@extends('layouts.main')

@section('content')
    <h1>Mi Contenido</h1>
    <p>Este es el contenido de mi página.</p>
@endsection
```

### Secciones

Las secciones permiten definir bloques de contenido que se insertan en los puntos designados del layout:

```php
<!-- Definir una sección con contenido -->
@section('sidebar')
    <div class="sidebar">
        <h3>Enlaces Rápidos</h3>
        <ul>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/profile">Perfil</a></li>
            <li><a href="/settings">Configuración</a></li>
        </ul>
    </div>
@endsection

<!-- Definir una sección de una línea -->
@section('title', 'Página de Inicio')

<!-- Renderizar una sección en un layout -->
@yield('title', 'Valor Predeterminado')
```

### Inclusión de Vistas Parciales

Para reutilizar componentes de interfaz, puedes incluir vistas parciales:

```php
<!-- Incluir una vista parcial -->
@include('partials.header')

<!-- Incluir con parámetros -->
@include('partials.alert', ['type' => 'error', 'message' => 'Algo salió mal!'])

<!-- Incluir si existe -->
@includeIf('partials.analytics', ['userId' => $user->id])

<!-- Incluir basado en una condición -->
@includeWhen($user->isAdmin(), 'partials.admin-controls')
```

## Sintaxis del Motor de Plantillas

### Impresión de Variables

```php
<!-- Impresión básica (con escape automático) -->
<p>Hola, {{ $name }}</p>

<!-- Impresión sin escape -->
<div>{!! $htmlContent !!}</div>

<!-- Con un valor predeterminado -->
<p>Bienvenido, {{ $username ?? 'Invitado' }}</p>
```

### Estructuras de Control

LightEngine soporta todas las estructuras de control básicas de PHP con una sintaxis más limpia:

#### Condicionales

```php
<!-- If/Else básico -->
@if($age >= 18)
    <p>Eres mayor de edad.</p>
@else
    <p>Eres menor de edad.</p>
@endif

<!-- If/Elseif/Else -->
@if($score > 90)
    <p>¡Excelente!</p>
@elseif($score > 75)
    <p>¡Muy bien!</p>
@elseif($score > 60)
    <p>Aprobado</p>
@else
    <p>Necesita mejorar</p>
@endif

<!-- Unless (inverso de if) -->
@unless($user->isVerified())
    <p>Por favor, verifica tu cuenta de correo.</p>
@endunless

<!-- Switch -->
@switch($role)
    @case('admin')
        <p>Panel de administrador</p>
        @break
    @case('editor')
        <p>Panel de editor</p>
        @break
    @default
        <p>Panel de usuario</p>
@endswitch
```

#### Bucles

```php
<!-- For -->
@for($i = 0; $i < 10; $i++)
    <p>Iteración {{ $i }}</p>
@endfor

<!-- Foreach -->
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@endforeach

<!-- Foreach con índice -->
@foreach($users as $index => $user)
    <p>{{ $index + 1 }}: {{ $user->name }}</p>
@endforeach

<!-- Foreach con variable Empty -->
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@empty
    <p>No hay usuarios registrados.</p>
@endforeach

<!-- While -->
@while($condition)
    <p>Contenido del bucle while</p>
@endwhile
```

### Comentarios

```php
{{-- Este comentario no aparecerá en el HTML renderizado --}}
```

## Componentes y Slots

LightEngine soporta un sistema de componentes similar al de otros frameworks modernos:

### Definición de Componentes

```php
<!-- resources/views/components/alert.light.php -->
<div class="alert alert-{{ $type ?? 'info' }}">
    @if(isset($title))
        <strong>{{ $title }}</strong>
    @endif
    
    {{ $slot }}
    
    @if(isset($footer))
        <div class="alert-footer">
            {{ $footer }}
        </div>
    @endif
</div>
```

### Uso de Componentes

```php
@component('components.alert', ['type' => 'danger', 'title' => 'Error'])
    <p>Algo salió mal mientras procesábamos tu solicitud.</p>
    @slot('footer')
        <button>Cerrar</button>
    @endslot
@endcomponent
```

## Directivas Personalizadas

LightEngine permite crear directivas personalizadas para extender su funcionalidad:

```php
// En un proveedor de servicios
public function boot()
{
    LightEngine::directive('datetime', function($expression) {
        return "<?php echo date('Y-m-d H:i:s', strtotime($expression)); ?>";
    });
    
    LightEngine::directive('currency', function($expression) {
        return "<?php echo '€' . number_format($expression, 2); ?>";
    });
}
```

Uso en vistas:

```php
<p>Fecha actual: @datetime('now')</p>
<p>Precio: @currency($product->price)</p>
```

## Helpers para Vistas

### Acceso a Configuración

```php
<title>{{ config('app.name') }}</title>
```

### URLs y Enlaces

```php
<!-- URL básica -->
<a href="{{ url('/about') }}">Acerca de</a>

<!-- URL a una ruta nombrada -->
<a href="{{ route('users.show', ['id' => $user->id]) }}">Ver Perfil</a>

<!-- URL para assets -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<!-- URL actual -->
<p>Estás en: {{ currentUrl() }}</p>
```

### CSRF Protection

```php
<!-- Campo CSRF para formularios -->
@csrf

<!-- Equivalente a: -->
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

## Cache de Vistas

LightEngine puede cachear las vistas compiladas para mejorar el rendimiento:

```php
// En el archivo de configuración
return [
    'views' => [
        'cache' => [
            'enabled' => env('VIEW_CACHE', true),
            'path' => storage_path('framework/views'),
        ],
    ],
];
```

Ten en cuenta que cuando se leen valores desde variables de entorno (a través de `env()`), todos los valores se devuelven como cadenas. El framework convierte automáticamente el valor de cadena "false" a un booleano `false` utilizando `filter_var($value, FILTER_VALIDATE_BOOLEAN)`. Esto asegura un manejo adecuado de los valores booleanos desde los archivos de entorno.

## Ejemplos Avanzados

### Formulario con Validación

```php
@extends('layouts.main')

@section('content')
    <h1>Crear Nuevo Usuario</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('users.store') }}" method="post">
        @csrf
        
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                class="form-control {{ hasError('name') ? 'is-invalid' : '' }}"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                class="form-control {{ hasError('email') ? 'is-invalid' : '' }}"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control {{ hasError('password') ? 'is-invalid' : '' }}"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">Confirmar Contraseña:</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-control"
            >
        </div>
        
        <button type="submit" class="btn btn-primary">Crear Usuario</button>
    </form>
@endsection
```

### Tabla de Datos con Paginación

```php
@extends('layouts.main')

@section('content')
    <h1>Usuarios</h1>
    
    <form action="{{ route('users.index') }}" method="get" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                value="{{ request()->query('search') }}" 
                class="form-control" 
                placeholder="Buscar usuarios..."
            >
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </div>
    </form>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Registrado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ formatDate($user->created_at) }}</td>
                    <td>
                        <a href="{{ route('users.show', ['id' => $user->id]) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('users.edit', ['id' => $user->id]) }}" class="btn btn-sm btn-primary">Editar</a>
                        
                        <form action="{{ route('users.destroy', ['id' => $user->id]) }}" method="post" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            
            @if(count($users) === 0)
                <tr>
                    <td colspan="5" class="text-center">No se encontraron usuarios</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <div class="pagination-container">
        {{ $users->links() }}
    </div>
@endsection
```

### Dashboard con Widgets

```php
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3">
            @component('components.stat-card')
                @slot('title')
                    Usuarios
                @endslot
                @slot('value')
                    {{ $totalUsers }}
                @endslot
                @slot('icon')
                    <i class="fas fa-users"></i>
                @endslot
                @slot('color')
                    primary
                @endslot
            @endcomponent
        </div>
        
        <div class="col-md-3">
            @component('components.stat-card')
                @slot('title')
                    Productos
                @endslot
                @slot('value')
                    {{ $totalProducts }}
                @endslot
                @slot('icon')
                    <i class="fas fa-box"></i>
                @endslot
                @slot('color')
                    success
                @endslot
            @endcomponent
        </div>
        
        <div class="col-md-3">
            @component('components.stat-card')
                @slot('title')
                    Pedidos
                @endslot
                @slot('value')
                    {{ $totalOrders }}
                @endslot
                @slot('icon')
                    <i class="fas fa-shopping-cart"></i>
                @endslot
                @slot('color')
                    info
                @endslot
            @endcomponent
        </div>
        
        <div class="col-md-3">
            @component('components.stat-card')
                @slot('title')
                    Ingresos
                @endslot
                @slot('value')
                    {{ formatCurrency($totalRevenue) }}
                @endslot
                @slot('icon')
                    <i class="fas fa-euro-sign"></i>
                @endslot
                @slot('color')
                    warning
                @endslot
            @endcomponent
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ventas Recientes</h5>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Últimos Usuarios</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($recentUsers as $user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <small class="d-block text-muted">{{ $user->email }}</small>
                                </div>
                                <span class="badge badge-primary badge-pill">{{ formatTimeAgo($user->created_at) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('sales-chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($salesChartData['labels']) !!},
                    datasets: [{
                        label: 'Ventas',
                        data: {!! json_encode($salesChartData['values']) !!},
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
@endsection
```

## Seguridad

### Escape Automático

Por defecto, LightEngine escapa automáticamente todas las variables para prevenir ataques XSS:

```php
// Esta salida será escapada para prevenir XSS
{{ $userInput }}

// Para mostrar HTML sin escapar (usar con precaución)
{!! $trustedHtml !!}
```

### Protección CSRF

Para proteger tus formularios contra ataques CSRF:

```php
<form method="POST" action="/profile">
    @csrf
    <!-- Campos del formulario -->
    <button type="submit">Guardar</button>
</form>
```

## Optimización

### Cache de Vistas

El motor LightEngine puede compilar y cachear las plantillas para mejorar el rendimiento:

```php
// En el archivo de configuración (config/views.php)
return [
    'cache' => [
        'enabled' => env('VIEW_CACHE_ENABLED', true),
        'path' => storage_path('framework/views'),
    ],
];
```

### Conversión de Booleanos desde Variables de Entorno

Cuando configuras `enabled` como `false` en tu archivo `.env`, el valor se almacena como la cadena "false", pero el framework lo convierte automáticamente a un valor booleano utilizando `filter_var($value, FILTER_VALIDATE_BOOLEAN)`:

```php
// En ViewServiceProvider.php
$cacheEnabled = filter_var(config('view.cache.enabled', false), FILTER_VALIDATE_BOOLEAN);
if ($cacheEnabled) {
    // Inicializar sistema de caché...
}
```

Esto asegura un manejo adecuado de tipos independientemente de cómo se proporcione la configuración. El mismo principio se aplica a otras configuraciones booleanas en el framework.

## Buenas Prácticas

1. **Mantén la Lógica Fuera de las Vistas**: Las vistas deben contener principalmente HTML y estructuras de control simples. La lógica compleja debe estar en controladores o servicios.

2. **Usa Layouts y Componentes**: Evita duplicar código utilizando plantillas, secciones y componentes.

3. **Nombra tus Vistas Claramente**: Usa nombres descriptivos siguiendo una convención coherente (`users.index`, `users.show`, etc.).

4. **Estructura tus Directorios**: Organiza tus vistas en subdirectorios lógicos que reflejen la estructura de tu aplicación.

5. **Utiliza Comentarios**: Documenta secciones complejas o importantes de tus vistas con comentarios `{{-- comentario --}}`.

6. **Valida la Entrada del Usuario**: Nunca confíes en los datos del usuario; valídalos antes de pasarlos a las vistas.

7. **Mantén las Vistas DRY (Don't Repeat Yourself)**: Extrae código repetitivo en parciales o componentes.

## Conclusión

El sistema de vistas y plantillas de LightWeight proporciona una forma potente y flexible de crear interfaces de usuario dinámicas y reutilizables. Con su sintaxis limpia y sus características avanzadas, LightEngine te permite separar claramente la lógica de presentación del resto de tu aplicación, lo que facilita el mantenimiento y la colaboración en proyectos de cualquier tamaño.

### Renderización Avanzada de Vistas

**Usando un layout específico:**

```php
// Usar un layout específico
return view('users.profile', ['user' => $user], 'user_layout');
```

**Renderizar sin layout:**

```php
// Desactivar completamente la renderización del layout (útil para respuestas AJAX o páginas de error)
return view('users.partial', ['user' => $user], false);
```

**Usando un layout condicionalmente:**

```php
// Determinar el layout basado en el tipo de solicitud
$layout = $request->ajax() ? false : 'main';
return view('content', $data, $layout);
```

La función `view()` internamente llama a `Response::view()` que puede aceptar:
- Nombre de layout como `string` - para usar un layout específico
- `false` (booleano) - para desactivar completamente el layout
- `null` - para usar el layout predeterminado

Esta flexibilidad es particularmente útil al renderizar páginas de error o respuestas AJAX.

> 🌐 [English Documentation](../en/views-templating.md)

## Estructura Interna del Motor de Vistas

### Proceso de Resolución de Plantillas

Al renderizar una vista, LightWeight sigue este proceso de resolución:

1. Convierte la notación de puntos a rutas de directorios (p.ej., `users.profile` → `users/profile`)
2. Busca el archivo de vista en el directorio de vistas del usuario
3. Si no lo encuentra, busca en el directorio de plantillas predeterminado
4. Si lo encuentra, renderiza el contenido de la vista
5. Si se especifica un layout (y no es `false`), renderiza el layout
6. Reemplaza la anotación de contenido en el layout con el contenido de la vista

Este proceso es manejado por el método `findViewFile()` que busca en ambas ubicaciones de plantillas, del usuario y predeterminadas:

```php
protected function findViewFile(string $path): ?string
{
    // Primero intenta en el directorio de vistas del usuario
    $userViewPath = "{$this->viewsDirectory}/$path.php";
    
    if (file_exists($userViewPath)) {
        return $userViewPath;
    }
    
    // Si no se encuentra, intenta en las plantillas predeterminadas
    $defaultViewPath = $this->getDefaultTemplatesDirectory() . "/$path.php";
    
    if (file_exists($defaultViewPath)) {
        return $defaultViewPath;
    }
    
    return null;
}
```

### Manejo de Excepciones

El motor de plantillas está diseñado para proporcionar mensajes de error útiles cuando no se pueden encontrar plantillas o layouts:

```php
if (!$viewPath) {
    throw new \RuntimeException("Vista no encontrada: $view.php");
}
```

El manejador de excepciones del framework convertirá estos errores en páginas de error amigables para el usuario.

## Manejo de Errores y Vistas

### Plantillas de Páginas de Error

LightWeight incluye plantillas de error predeterminadas para errores HTTP comunes. Estas plantillas se almacenan en el directorio de plantillas predeterminado y se utilizan cuando:

1. Ocurre una excepción o error en tu aplicación
2. El manejador de excepciones renderiza una respuesta de error
3. Tu aplicación no tiene una vista de error personalizada definida

El framework incluye plantillas para:
- Errores 404 Not Found (No encontrado)
- Errores 500 Internal Server (Error interno del servidor)
- Errores 403 Forbidden (Prohibido)
- Visualización general de errores

### Personalización de Páginas de Error

Puedes personalizar las páginas de error creando tus propias plantillas de vistas de error:

```
resources/
  views/
    errors/
      404.light.php
      500.light.php
      403.light.php
```

El manejador de excepciones buscará vistas en este orden:
1. Una ruta de vista personalizada especificada en la configuración de excepciones
2. La vista de error predeterminada para el tipo específico de excepción
3. Un respaldo a las plantillas de error predeterminadas

### Renderizado de Páginas de Error Sin Layouts

Las páginas de error se renderizan sin layouts por defecto para prevenir excepciones anidadas (donde el propio layout podría estar causando errores):

```php
protected function renderHttpNotFound(HttpNotFoundException $e): ResponseContract
{
    try {
        $view = config('exceptions.views.not_found', 'errors.404');
        // Pasar false como layout para evitar la renderización del layout
        return Response::view($view, [], false)->setStatus(404);
    } catch (\Throwable $viewError) {
        // Respaldo a respuesta de texto si falla la renderización de la vista
        return Response::text('404 No Encontrado: ' . $e->getMessage())->setStatus(404);
    }
}
```

Esto asegura que incluso si hay un problema con tus vistas o plantillas, el usuario seguirá recibiendo una respuesta de error apropiada.
