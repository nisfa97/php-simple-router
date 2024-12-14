# PHP Simple Router
A lightweight and developer-friendly router for PHP, utilizing PHP's Attribute feature. This router allows you to define routes directly within your controller files, eliminating the need for separate route files.

**Note:** This router is designed for small or personal projects. It lacks advanced features required for large-scale or production-ready applications.

---

## Features
- **Supported HTTP Methods**: GET, POST, PUT, PATCH, DELETE, OPTIONS.
- **Route Parameters**: Dynamically capture route segments.
- **Route Prefixing**: Group routes under a common prefix.
- **Middleware Support**: Add middleware for request handling.
- **Built-in Dependency Injection (DI) Container**: Simplifies dependency management.
- **Route Caching (Coming Soon)**: Improve performance by caching routes.

---

## Quick Start

### Basic Example
The `match()` method processes incoming requests and returns a response string.

```php
use Nisfa97\PhpSimpleRouter\Router;
use Nisfa97\PhpSimpleRouter\Container;

$router = new Router();

// Register a single controller
$router->registerControllers(DashboardController::class);

// Register multiple controllers
$router->registerControllers([
    AuthController::class,
    DashboardController::class,
]);

// Register dependencies
$router->registerContainer(function (Container $c) {
    $c->bind(Request::class, fn () => new Request());
    $c->bind(Response::class, fn () => new Response());
});

// Register middlewares
$router->registerMiddlewares([
    WithCsrf::class,
    Auth::class,
    EmailConfirmed::class,
]);

$response = $router->match();

echo $response;
```

### Simplified Inline Example
For more compact code:

```php
use Nisfa97\PhpSimpleRouter\Router;

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

---

## Route Attributes

### Basic Routing
Define routes within your controllers using PHP Attributes:

```php
use Nisfa97\PhpSimpleRouter\Attributes\Routing\Route;

class BookController {
    #[Route(method:'GET', uri:'/books')]
    public function index(): string {
        return 'books.index';
    }
}
```

**Alternate Syntax:**

- Use lowercase methods:
  ```php
  #[Route(method:'get', uri:'/books')]
  public function index(): string {
      return 'books.index';
  }
  ```

- Use predefined constants:
  ```php
  #[Route(Route::METHOD_GET, '/books')]
  public function index(): string {
      return 'books.index';
  }
  ```

### Supported HTTP Methods
- `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`.

Example:
```php
#[Route(Route::METHOD_GET, '/uri')]
#[Route(Route::METHOD_POST, '/uri')]
#[Route(Route::METHOD_PUT, '/uri')]
#[Route(Route::METHOD_PATCH, '/uri')]
#[Route(Route::METHOD_DELETE, '/uri')]
#[Route(Route::METHOD_OPTIONS, '/uri')]
```

### Route Parameters
Capture URI segments dynamically:

```php
#[Route(method:'GET', uri:'/books/{id}')]
public function show($id): string {
    return "Book: $id";
}
```
Parameters in the URI (e.g., `{id}`) are automatically injected into the method.

### Route Prefixes
Group routes under a common prefix:

```php
use Nisfa97\PhpSimpleRouter\Attributes\Routing\RoutePrefix;

#[RoutePrefix(prefix:'admin')]
class BookController {
    #[Route(method:'GET', uri:'/books')]
    public function index() {}
}
```

**Options:**

- Specify methods to prefix:
  ```php
  #[RoutePrefix(prefix:'admin', only:['index', 'show'])]
  class BookController {
      #[Route(method:'GET', uri:'/books')]
      public function index() {}

      #[Route(method:'GET', uri:'/books/{id}')]
      public function show($id) {}

      #[Route(method:'POST', uri:'/books')]
      public function store() {}
  }
  ```

- Exclude methods from prefixing:
  ```php
  #[RoutePrefix(prefix:'admin', except:['store'])]
  class BookController {
      #[Route(method:'GET', uri:'/books')]
      public function index() {}

      #[Route(method:'POST', uri:'/books')]
      public function store() {}
  }
  ```

---

## Middleware

### Attaching Middleware to Routes
Add middleware to specific routes:

```php
#[Route(method:'GET', uri:'/books', middlewares: ['auth'])]
public function index() {}
```

### Define Middleware
Middleware classes must have a `handle` method:

```php
class ConfirmedEmail {
    public function handle(callable $next) {
        return function () use ($next) {
            // Logic here...

            return $next();
        };
    }
}
```

### Register Middleware

```php
// Register globally
$router->registerMiddlewares([
    WithCsrf::class,
    Auth::class,
]);

// Register with alias
$router->registerMiddlewares([
    'auth' => [
        Auth::class,
        IsAdmin::class
    ]
]);
```

---

## Upcoming Features
- **Route Caching**: Cache routes to enhance performance.
- **Middleware Enhancements**: Prepend/append middleware and middleware grouping.
- **DI Container Enhancements**: Additional features for dependency injection.

---

## Limitations
- Designed for small or personal projects.
- Limited features compared to frameworks like Laravel or Symfony.

---

## Contributions
Contributions are welcome! Fork the repository and submit pull requests for improvements or bug fixes.

**Made with ❤️ by Nisfa97**

