# PHP Simple Router
A lightweight and developer-friendly router for PHP, utilizing PHP's Attribute feature. This router allows you to define routes directly within your controller files, eliminating the need for separate route files.

Note: This router is designed for small or personal projects. It lacks advanced features required for large-scale or production-ready applications.

## Features
1. Supported HTTP Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
3. Route Prefixing (Coming Soon): A planned feature to group routes under a common prefix.
4. Middleware Support: Add middleware for request handling.
5. Built-in Dependency Injection (DI) Container: Simplifies dependency management.
6. Route Caching (Coming Soon): Improve performance by caching routes.

## Usage

### Basic Example
The match() method processes incoming requests and returns a response string.

```php
use Nisfa97\PhpSimpleRouter\Router;

require './vendor/autoload.php';

$router = new Router();

// register single controller
$router->registerControllers(DashboardController::class);

// register multiple controllers
$router->registerControllers([
    AuthController::class,
    DashboardController::class,
]);

// register dependencies here...
$router->registerContainer([
    Request::class,
    Response::class,
]);

// register middlewares here...
$router->registerMiddlewares([
    WithCsrf::class,
    Auth::class,
    EmailConfirmed::class,
]);

$response = $router->match();

echo $response;
```

### Simplified Inline Example
For more compact code, you can chain method calls:

```php
use Nisfa97\PhpSimpleRouter\Router;

require './vendor/autoload.php';

$response = (new Router())
    ->registerControllers([
        DashboardController::class
    ])
    ->registerContainer([
        Request::class,
        Response::class,
    ])
    ->registerMiddlewares([
        WithCsrf::class,
        Auth::class,
        EmailConfirmed::class,
    ])
    ->match();

echo $response;
```

## Attributes
This router uses PHP's Attributes to define routes within your controllers.

### Example

```php
use Nisfa97\PhpSimpleRouter\Attributes\Routing\Route;

/** HTTP method can also be written in lowercase:
 * #[Route('get', '/dashboard')]
 */
class DashboardController {
    #[Route(method:'GET', uri:'/dashboard')]
    public function index(): string {
        return 'Welcome to the Dashboard!';
    }
}

// Alternatively:
class DashboardController {
    /**
     * Route class supports several HTTP constants:
     * Route::METHOD_GET
     * Route::METHOD_POST
     * Route::METHOD_PUT
     * Route::METHOD_PATCH
     * Route::METHOD_DELETE
     * Route::METHOD_OPTIONS
     */

    #[Route(method:Route::METHOD_GET, uri:'/dashboard')]
    public function index(): string {
        return 'Welcome to the Dashboard!';
    }
}

// Attach middleware to a specific route:
class DashboardController {
    #[Route(method:Route::METHOD_GET, uri:'/dashboard', middlewares:['auth'])]
    public function index(): string {
        return 'Welcome to the Dashboard!';
    }
}
```

## Middlewares
Middleware can be registered globally or with aliases for specific routes.

### Example
```php
// register single middleware globally
->registerMiddlewares(ConfirmedEmail::class);

// register group of middleware globally
->registerMiddlewares([
    WithCsrf::class,
    Auth::class,
    EmailConfirmed::class,
]);

// Register middleware with alias name.
// These middlewares will only run if a controller has them registered.
->registerMiddlewares([
    'auth' => [
        Auth::class,
        IsAdmin::class
    ]
]);
```

## Features in Progress
- Route Prefixing: A feature to group and manage routes under common prefixes.
- Route Caching: Cache routes for improved performance.
- Prepend and Append Middleware: Add middleware at specific points in the request pipeline.
- Additional enhancements for middleware and DI containers.

## Limitations
- Not suitable for production-grade projecst yet.
- Limited feature set compared to larger frameworks like Laravel or Symfony.

## Contributions
Contributions are welcome! Feel free to fork the repository and submit pull requests for enhancements or bug fixes. 

Made with ❤️ by Nisfa97
