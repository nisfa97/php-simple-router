<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteCollectionException;
use ReflectionClass;

class RouteCollection
{
    private array $routes = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function registerController(string $controller): void
    {
        if (! class_exists($controller)) {
            throw RouteCollectionException::classNotFound($controller);
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

    private function generateUriPattern(string $uri): string
    {
        return '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
    }
}
