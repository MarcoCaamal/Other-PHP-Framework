# Request and Response Handling System

## Introduction

The request and response handling system is a fundamental part of any modern web framework. LightWeight provides a robust and flexible implementation that makes it easy to handle incoming HTTP requests and generate appropriate responses.

## The Request Class

The `Request` class represents an incoming HTTP request and provides a clean interface to access all data associated with that request.

### Creating a Request Instance

LightWeight automatically creates a `Request` instance for each incoming HTTP request. This instance is available in your controllers through dependency injection:

```php
use LightWeight\Http\Request;

public function store(Request $request)
{
    // Work with the request
}
```

### Accessing Request Data

#### Form/POST Data

To access data sent through a form or POST request:

```php
// Get all POST data
$allData = $request->data();

// Get a specific value
$name = $request->data('name');

// With default value if it doesn't exist
$page = $request->data('page') ?? 1;
```

#### Query String Parameters

To access URL parameters (`?param=value`):

```php
// All query parameters
$queryParams = $request->query();

// A specific parameter
$search = $request->query('search');

// With default value
$sort = $request->query('sort') ?? 'name';
```

#### Route Parameters

Parameters defined in routes (like `/users/{id}`) are available through:

```php
// Get all route parameters
$routeParams = $request->routeParameters();

// Get a specific parameter
$id = $request->routeParameters('id');
```

#### HTTP Headers

To access request headers:

```php
// All headers
$headers = $request->headers();

// A specific header
$contentType = $request->headers('Content-Type');
$userAgent = $request->headers('User-Agent');
```

#### Uploaded Files

To work with uploaded files:

```php
// Check if a file has been uploaded
if ($request->file('avatar')) {
    // Get the file instance
    $file = $request->file('avatar');
    
    // Check if it's an image
    if ($file->isImage()) {
        // It's an image
    }
    
    // Get the extension
    $extension = $file->extension();
    
    // Store in a predefined path
    $path = $file->store('avatars');
}
```

### Data Validation

LightWeight integrates a validation system for input data:

```php
// Validate data with rules
try {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'age' => 'integer|min:18|max:100',
    ]);
    
    // Data is valid, continue processing
} catch (\LightWeight\Validation\Exceptions\ValidationException $e) {
    // Validation failed
    $errors = $e->errors;
    // Handle errors
}
```

## The Response Class

The `Response` class represents the HTTP response that will be sent to the client after processing the request.

### Creating Basic Responses

```php
use LightWeight\Http\Response;

// Response with content and status code
$response = new Response();
$response->setContent('Content');
$response->setStatus(200);

// Set headers
$response->setHeader('Content-Type', 'text/plain');
$response->setHeaders([
    'X-Custom-Header' => 'Value',
    'X-Another-Header' => 'Another Value',
]);

// Remove a header
$response->removeHeader('X-Custom-Header');
```

### Common Response Types

#### Views

To return a view:

```php
// In a controller method
return view('users.profile', ['user' => $user]);

// With compact data
$user = User::find($id);
return view('users.profile', compact('user'));
```

#### JSON

To return JSON data:

```php
// Automatic conversion of arrays and objects to JSON
return json([
    'name' => 'John',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user'],
]);

// With custom status code
return json(['error' => 'Not found'], 404);
```

#### Plain Text

```php
return Response::text('Plain text content');
```

#### Redirects

```php
// Simple redirect
return redirect('/dashboard');

// Redirect back (to previous page)
return back();
```

## Advanced Request and Response Handling

### Request/Response Middleware

Middlewares can modify the request before it reaches the controller, or modify the response before it's sent to the client:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Response;

class CorsMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // If it's an OPTIONS request (preflight), return immediate response
        if ($request->method()->value === 'OPTIONS') {
            $response = new Response();
            $response->setStatus(200);
        } else {
            // Process the request normally
            $response = $next($request);
        }
        
        // Modify the response to add CORS headers
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }
}
```

### Intercepting the Request

To intercept and manipulate the request before it reaches a controller:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class TransformInputMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Transform input data (for example, trim whitespace)
        $input = $request->data();
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }
        
        // Replace input data with transformed data
        $request->setPostData($input);
        
        return $next($request);
    }
}
```

## Best Practices

1. **Early Validation**: Validate input data at the beginning of controller methods.

2. **Consistent Responses**: Maintain a consistent format for all your responses, especially in APIs.

3. **Appropriate Status Codes**: Use appropriate HTTP status codes for responses.

4. **Security**: Always sanitize input data to avoid security issues.

5. **Error Handling**: Use try-catch to handle exceptions and return appropriate error responses.

## Advanced Examples

### RESTful API

```php
<?php

namespace App\Controllers\Api;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use App\Models\Product;

class ProductController extends ControllerBase
{
    public function __construct()
    {
        $this->setMiddlewares([
            \App\Middleware\ApiAuthMiddleware::class,
        ]);
    }
    
    public function index(Request $request): Response
    {
        $products = DB::table('products')->select('*');
        
        return json([
            'data' => $products,
        ]);
    }
    
    public function store(Request $request): Response
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);
            
            DB::table('products')->insert($validated);
            
            return json([
                'message' => 'Product created successfully'
            ])->setStatus(201);
            
        } catch (\Throwable $e) {
            return json([
                'message' => 'Error creating product',
                'errors' => $e->getMessage(),
            ])->setStatus(422);
        }
    }
    
    // Other methods for show, update, destroy...
}
```

### Form Handling with File Upload

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class ProfileController extends ControllerBase
{
    public function update(Request $request, $id): Response
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'bio' => 'nullable|string|max:1000',
            ]);
            
            // Handle avatar upload
            $avatarPath = null;
            if ($request->file('avatar')) {
                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars');
            }
            
            // Update user in database
            DB::table('users')
                ->where('id', $id)
                ->update([
                    ...$validated,
                    'avatar_path' => $avatarPath ?? DB::raw('avatar_path')
                ]);
            
            // Redirect with success message
            return redirect('/profile');
            
        } catch (\Exception $e) {
            return redirect('/profile/edit')->withErrors([
                'error' => 'Error updating profile'
            ]);
        }
    }
}
```

> 🌐 [Documentación en Español](../es/request-response-handling.md)
