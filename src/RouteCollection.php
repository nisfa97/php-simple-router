<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteCollectionException;
use ReflectionClass;

class RouteCollection
{
    private array $routes = [];

    public function __construct(
        private ?Middleware $middleware = null,
    ) {
        if ($middleware === null) {
            $this->middleware = new Middleware();
        }
    }

    // Routes
    public function registerController(string $controller): void
    {
        if (! class_exists($controller)) {
            throw RouteCollectionException::classNotFound($controller);
        }

        if (in_array($controller, $this->routes)) {
            return;
        }

        $reflector = new ReflectionClass($controller);

        foreach ($reflector->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                if ($attribute->getName() === 'Nisfa97\PhpSimpleRouter\Route') {
                    $routeInstance = $attribute->newInstance();

                    $this->routes[strtoupper($routeInstance->method)][] = [
                        'uri'           => $this->generateUriPattern($routeInstance->uri),
                        'callback'      => [$controller, $method->getName()],
                    ];
                }
            }
        }
    }

    public function registerGroup(array $controllers): void
    {
        foreach ($controllers as $controller) {
            $this->registerController($controller);
        }
    }

    private function generateUriPattern(string $uri): string
    {
        return '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
    }

    // Middlewares
    public function registerMiddlewares(array $middlewares): void
    {
        $this->middleware->addMiddlewares($middlewares);
    }

    // Get private properties
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getMiddlewares(): array
    {
        return $this->middleware->getMiddlewares();
    }

    public function getAll(): array
    {
        return [
            'routes'        => $this->routes,
            'middlewares'   => $this->middleware->getMiddlewares()
        ];
    }
}
