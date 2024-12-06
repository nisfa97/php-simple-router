<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteCollectionException;
use ReflectionClass;

class RouteCollection
{
    private array $routes = [];
    private array $middlewares = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

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
                $routeInstance = $attribute->newInstance();

                $this->routes[strtoupper($routeInstance->method)][] = [
                    'uri'       => $this->generateUriPattern($routeInstance->uri),
                    'callback'  => [$controller, $method->getName()]
                ];
            }
        }
    }

    public function registerGroup(array $controllers): void
    {
        foreach ($controllers as $controller) {
            $this->registerController($controller);
        }
    }

    public function registerMiddlewares(array $middlewares): void
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
    }

    private function generateUriPattern(string $uri): string
    {
        return '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
    }
}
