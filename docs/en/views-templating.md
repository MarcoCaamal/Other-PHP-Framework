# Views and Templates System in LightWeight

## Introduction

The LightWeight views and templates system allows you to separate your application's presentation logic from business logic. LightWeight uses the "LightEngine" template engine, a lightweight and efficient implementation designed specifically for this framework, offering a perfect balance between simplicity and power.

## Basic Concepts

### Directory Structure

Views in LightWeight are typically organized in the `resources/views` directory. Within this directory, you can organize your views into subdirectories to maintain a clear structure:

```
resources/
  views/
    layouts/
      main.light.php
    partials/
      header.light.php
      footer.light.php
    users/
      index.light.php
      show.light.php
      edit.light.php
    errors/
      404.light.php
      500.light.php
```

### Basic View Rendering

To render a view from a controller:

```php
public function index()
{
    $users = User::all();
    return view('users.index', ['users' => $users]);
}
```

The `view()` function takes as its first parameter the relative path to the view (using dot as a separator) and as an optional second parameter an array of data to pass to the view.

### View File Structure

View files in LightWeight have the `.light.php` extension and are basically PHP files with additional LightEngine-specific syntax:

```php
<!-- resources/views/users/index.light.php -->
@extends('layouts.main')

@section('title', 'User List')

@section('content')
    <h1>Users</h1>
    
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
        <p>No registered users.</p>
    @endif
@endsection
```

## Templates and Layouts

### Defining a Layout

A layout is a master template that defines the general structure of your pages:

```php
<!-- resources/views/layouts/main.light.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Application')</title>
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

### Extending Layouts

To use a layout in a view, use the `@extends` directive:

```php
@extends('layouts.main')

@section('content')
    <h1>My Content</h1>
    <p>This is my page content.</p>
@endsection
```

### Sections

Sections allow you to define blocks of content that are inserted at designated points in the layout:

```php
<!-- Define a section with content -->
@section('sidebar')
    <div class="sidebar">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/profile">Profile</a></li>
            <li><a href="/settings">Settings</a></li>
        </ul>
    </div>
@endsection

<!-- Define a one-line section -->
@section('title', 'Home Page')

<!-- Render a section in a layout -->
@yield('title', 'Default Value')
```

### Including Partial Views

To reuse interface components, you can include partial views:

```php
<!-- Include a partial view -->
@include('partials.header')

<!-- Include with parameters -->
@include('partials.alert', ['type' => 'error', 'message' => 'Something went wrong!'])

<!-- Include if exists -->
@includeIf('partials.analytics', ['userId' => $user->id])

<!-- Include based on a condition -->
@includeWhen($user->isAdmin(), 'partials.admin-controls')
```

## Template Engine Syntax

### Printing Variables

```php
<!-- Basic printing (with automatic escaping) -->
<p>Hello, {{ $name }}</p>

<!-- Printing without escaping -->
<div>{!! $htmlContent !!}</div>

<!-- With a default value -->
<p>Welcome, {{ $username ?? 'Guest' }}</p>
```

### Control Structures

LightEngine supports all basic PHP control structures with a cleaner syntax:

#### Conditionals

```php
<!-- Basic If/Else -->
@if($age >= 18)
    <p>You are an adult.</p>
@else
    <p>You are a minor.</p>
@endif

<!-- If/Elseif/Else -->
@if($score > 90)
    <p>Excellent!</p>
@elseif($score > 75)
    <p>Very good!</p>
@elseif($score > 60)
    <p>Passed</p>
@else
    <p>Needs improvement</p>
@endif

<!-- Unless (inverse of if) -->
@unless($user->isVerified())
    <p>Please verify your email account.</p>
@endunless

<!-- Switch -->
@switch($role)
    @case('admin')
        <p>Admin panel</p>
        @break
    @case('editor')
        <p>Editor panel</p>
        @break
    @default
        <p>User panel</p>
@endswitch
```

#### Loops

```php
<!-- For -->
@for($i = 0; $i < 10; $i++)
    <p>Iteration {{ $i }}</p>
@endfor

<!-- Foreach -->
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@endforeach

<!-- Foreach with index -->
@foreach($users as $index => $user)
    <p>{{ $index + 1 }}: {{ $user->name }}</p>
@endforeach

<!-- Foreach with Empty variable -->
@foreach($users as $user)
    <p>{{ $user->name }}</p>
@empty
    <p>No registered users.</p>
@endforeach

<!-- While -->
@while($condition)
    <p>While loop content</p>
@endwhile
```

### Comments

```php
{{-- This comment will not appear in the rendered HTML --}}
```

## Components and Slots

LightEngine supports a component system similar to that of other modern frameworks:

### Defining Components

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

### Using Components

```php
@component('components.alert', ['type' => 'danger', 'title' => 'Error'])
    <p>Something went wrong while processing your request.</p>
    @slot('footer')
        <button>Close</button>
    @endslot
@endcomponent
```

## Custom Directives

LightEngine allows you to create custom directives to extend its functionality:

```php
// In a service provider
public function boot()
{
    LightEngine::directive('datetime', function($expression) {
        return "<?php echo date('Y-m-d H:i:s', strtotime($expression)); ?>";
    });
    
    LightEngine::directive('currency', function($expression) {
        return "<?php echo '$' . number_format($expression, 2); ?>";
    });
}
```

Usage in views:

```php
<p>Current date: @datetime('now')</p>
<p>Price: @currency($product->price)</p>
```

## View Helpers

### Configuration Access

```php
<title>{{ config('app.name') }}</title>
```

### URLs and Links

```php
<!-- Basic URL -->
<a href="{{ url('/about') }}">About</a>

<!-- URL to a named route -->
<a href="{{ route('users.show', ['id' => $user->id]) }}">View Profile</a>

<!-- URL for assets -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<!-- Current URL -->
<p>You are at: {{ currentUrl() }}</p>
```

### CSRF Protection

```php
<!-- CSRF field for forms -->
@csrf

<!-- Equivalent to: -->
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

## View Caching

LightEngine can cache compiled views to improve performance:

```php
// In the configuration file
return [
    'views' => [
        'cache' => [
            'enabled' => env('VIEW_CACHE', true),
            'path' => storage_path('framework/views'),
        ],
    ],
];
```

## Advanced Examples

### Form with Validation

```php
@extends('layouts.main')

@section('content')
    <h1>Create New User</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('users.store') }}" method="post">
        @csrf
        
        <div class="form-group">
            <label for="name">Name:</label>
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
            <label for="password">Password:</label>
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
            <label for="password_confirmation">Confirm Password:</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-control"
            >
        </div>
        
        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
@endsection
```

### Data Table with Pagination

```php
@extends('layouts.main')

@section('content')
    <h1>Users</h1>
    
    <form action="{{ route('users.index') }}" method="get" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                value="{{ request()->query('search') }}" 
                class="form-control" 
                placeholder="Search users..."
            >
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered</th>
                <th>Actions</th>
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
                        <a href="{{ route('users.show', ['id' => $user->id]) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('users.edit', ['id' => $user->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                        
                        <form action="{{ route('users.destroy', ['id' => $user->id]) }}" method="post" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            
            @if(count($users) === 0)
                <tr>
                    <td colspan="5" class="text-center">No users found</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <div class="pagination-container">
        {{ $users->links() }}
    </div>
@endsection
```

### Dashboard with Widgets

```php
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3">
            @component('components.stat-card')
                @slot('title')
                    Users
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
                    Products
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
                    Orders
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
                    Revenue
                @endslot
                @slot('value')
                    {{ formatCurrency($totalRevenue) }}
                @endslot
                @slot('icon')
                    <i class="fas fa-dollar-sign"></i>
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
                    <h5 class="card-title">Recent Sales</h5>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Latest Users</h5>
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
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
                        label: 'Sales',
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

## Security

### Automatic Escaping

By default, LightEngine automatically escapes all variables to prevent XSS attacks:

```php
// This output will be escaped to prevent XSS
{{ $userInput }}

// To display HTML without escaping (use with caution)
{!! $trustedHtml !!}
```

### CSRF Protection

To protect your forms against CSRF attacks:

```php
<form method="POST" action="/profile">
    @csrf
    <!-- Form fields -->
    <button type="submit">Save</button>
</form>
```

## Optimization

### View Caching

The LightEngine engine can compile and cache templates to improve performance:

```php
// In the configuration file (config/views.php)
return [
    'cache' => [
        'enabled' => env('VIEW_CACHE_ENABLED', true),
        'path' => storage_path('framework/views'),
    ],
];
```

## Best Practices

1. **Keep Logic Out of Views**: Views should contain primarily HTML and simple control structures. Complex logic should be in controllers or services.

2. **Use Layouts and Components**: Avoid code duplication by using templates, sections, and components.

3. **Name Your Views Clearly**: Use descriptive names following a consistent convention (`users.index`, `users.show`, etc.).

4. **Structure Your Directories**: Organize your views into logical subdirectories that reflect your application's structure.

5. **Use Comments**: Document complex or important sections of your views with `{{-- comment --}}` comments.

6. **Validate User Input**: Never trust user data; validate it before passing it to views.

7. **Keep Views DRY (Don't Repeat Yourself)**: Extract repetitive code into partials or components.

## Conclusion

The LightWeight views and templates system provides a powerful and flexible way to create dynamic and reusable user interfaces. With its clean syntax and advanced features, LightEngine allows you to clearly separate presentation logic from the rest of your application, making it easier to maintain and collaborate on projects of any size.

> 🌐 [Documentación en Español](../es/views-templating.md)
