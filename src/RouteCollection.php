<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteCollectionException;
use ReflectionClass;

class RouteCollection
{
    private array $routes = [];

    public function setControllers(string|array $controllers): void
    {
        if (is_string($controllers)) {
            $this->addController($controllers);
            return;
        }

        foreach ($controllers as $controller) {
            $this->addController($controller);
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function addController(string $controller): void
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
                        'middlewares'   => $routeInstance->middlewares,
                    ];
                }
            }
        }
    }

    private function generateUriPattern(string $uri): string
    {
        return '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
    }
}
