# Controllers in LightWeight

## Introduction

Controllers are a fundamental part of the MVC (Model-View-Controller) pattern implemented in the LightWeight Framework. They act as intermediaries between models and views, processing HTTP requests, interacting with data, and returning appropriate responses.

## Basic Structure

In LightWeight, all controllers extend the `ControllerBase` base class. A typical controller has the following structure:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class UserController extends ControllerBase
{
    /**
     * Display a list of users
     */
    public function index(Request $request): Response
    {
        $users = User::all();
        
        return view('users.index', compact('users'));
    }
    
    /**
     * Show the form for creating a new user
     */
    public function create(): Response
    {
        return view('users.create');
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        User::create($validated);
        
        return redirect('/users')->withSuccess('User created successfully');
    }
    
    /**
     * Display the specified user
     */
    public function show(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('User not found');
        }
        
        return view('users.show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified user
     */
    public function edit(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('User not found');
        }
        
        return view('users.edit', compact('user'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('User not found');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
        
        $user->update($validated);
        
        return redirect('/users/'.$id)->withSuccess('User updated successfully');
    }
    
    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('User not found');
        }
        
        $user->delete();
        
        return redirect('/users')->withSuccess('User deleted successfully');
    }
}
```

## Creating Controllers

You can create a controller using the command line tool:

```bash
php light make:controller UserController
```

To create a controller with resource methods (index, create, store, show, edit, update, destroy):

```bash
php light make:controller UserController --resource
```

## Dependency Injection

LightWeight supports automatic dependency injection in controller methods:

```php
public function index(Request $request, UserService $userService): Response
{
    $users = $userService->getAllUsers();
    
    return view('users.index', compact('users'));
}
```

The framework will automatically resolve and inject the `UserService` instance when the `index` method is called.

## Controller Middleware

You can apply middleware to controllers in several ways:

### In the Constructor

```php
class UserController extends ControllerBase
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('log')->only(['store', 'update', 'destroy']);
        $this->middleware('subscribed')->except(['index', 'show']);
    }
}
```

### In the Route Definition

```php
Route::get('/users', [UserController::class, 'index'])->middleware('auth');
```

## Single Action Controllers

For simple cases, you can create a controller with only a single `__invoke` method:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class ShowDashboardController extends ControllerBase
{
    public function __invoke(Request $request): Response
    {
        $stats = $this->getDashboardStats();
        
        return view('dashboard', compact('stats'));
    }
    
    protected function getDashboardStats(): array
    {
        // Get dashboard statistics
        return [
            'users' => User::count(),
            'posts' => Post::count(),
            'comments' => Comment::count(),
        ];
    }
}
```

Route definition for single action controller:

```php
Route::get('/dashboard', ShowDashboardController::class);
```

## API Controllers

API controllers typically return JSON responses instead of HTML views:

```php
<?php

namespace App\Controllers\Api;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class UserApiController extends ControllerBase
{
    public function index(): Response
    {
        $users = User::all();
        
        return Response::json($users);
    }
    
    public function show($id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }
        
        return Response::json($user);
    }
    
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
        
        $user = User::create($validated);
        
        return Response::json($user, 201);
    }
    
    // Additional methods for update, destroy, etc.
}
```

## Request Validation

Controllers often need to validate incoming request data. LightWeight provides a convenient way to do this:

```php
public function store(Request $request): Response
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required',
        'published_at' => 'nullable|date',
        'category_id' => 'required|exists:categories,id',
    ]);
    
    $post = Post::create($validated);
    
    return redirect()->route('posts.show', $post->id);
}
```

If validation fails, the request will automatically redirect back with errors. In an API context, it will return a JSON response with validation errors.

## Returning Responses

Controllers can return various types of responses:

### View Responses

```php
return view('users.index', ['users' => $users]);
```

### JSON Responses

```php
return Response::json(['name' => 'John', 'email' => 'john@example.com']);
```

### Redirect Responses

```php
return redirect('/dashboard');
return redirect()->route('users.show', ['id' => $user->id]);
return redirect()->back();
return redirect()->with('status', 'Profile updated!');
```

### File Downloads

```php
return Response::download('/path/to/file.pdf', 'report.pdf');
```

### Custom Responses

```php
return Response::make('Custom content', 200, ['Content-Type' => 'text/plain']);
```

## Resource Controllers

Resource controllers provide a convenient way to organize CRUD operations with conventional naming:

```php
// Routes for a resource controller
Route::resource('posts', PostController::class);
```

This single line creates the following routes:

| HTTP Method | URI                | Action  | Route Name     |
|-------------|-------------------|---------|----------------|
| GET         | /posts            | index   | posts.index    |
| GET         | /posts/create     | create  | posts.create   |
| POST        | /posts            | store   | posts.store    |
| GET         | /posts/{id}       | show    | posts.show     |
| GET         | /posts/{id}/edit  | edit    | posts.edit     |
| PUT/PATCH   | /posts/{id}       | update  | posts.update   |
| DELETE      | /posts/{id}       | destroy | posts.destroy  |

You can limit the methods included in the resource route:

```php
Route::resource('posts', PostController::class, ['only' => ['index', 'show']]);
Route::resource('posts', PostController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);
```

## Controller Organization

For larger applications, organizing controllers into subdirectories can help maintain a clean structure:

```
app/
├── Controllers/
│   ├── Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── PostController.php
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   ├── UserManagementController.php
│   │   └── SettingsController.php
│   ├── Auth/
│   │   ├── LoginController.php
│   │   ├── RegisterController.php
│   │   └── ForgotPasswordController.php
│   ├── HomeController.php
│   ├── UserController.php
│   └── PostController.php
```

## Best Practices

1. **Keep controllers focused**: Each controller should handle a specific aspect of your application.
2. **Use resource controllers** for standard CRUD operations.
3. **Validate input data**: Always validate incoming request data to maintain data integrity and security.
4. **Avoid business logic in controllers**: Controllers should be thin and primarily coordinate the interaction between models and views. Move complex business logic to service classes.
5. **Use dependency injection** for services needed by the controller.
6. **Consistent naming**: Follow conventions like pluralized resource names (UsersController) and meaningful action names.
7. **Use type hints** for better code readability and error detection.
8. **Handle errors gracefully**: Provide appropriate responses for error cases.

## Related Topics

- [Routing Guide](routing-guide.md)
- [Request and Response Handling](request-response-handling.md)
- [Middleware Guide](middleware-guide.md)
- [Validation Guide](validation-guide.md)
