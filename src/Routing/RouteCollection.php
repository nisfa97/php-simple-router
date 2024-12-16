<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Routing;

use Nisfa97\PhpSimpleRouter\Attributes\Routing\Route;
use Nisfa97\PhpSimpleRouter\Attributes\Routing\RoutePrefix;
use Nisfa97\PhpSimpleRouter\Exceptions\RouteCollectionException;
use ReflectionAttribute;
use ReflectionClass;

class RouteCollection
{
    private array $routes = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function setControllers(string|array $controllers): void
    {
        foreach ((array) $controllers as $controller) {
            $this->addController($controller);
        }
    }

    private function addController(string $controller): void
    {
        if (empty($controller)) throw RouteCollectionException::emptyController();

        if (!class_exists($controller)) throw RouteCollectionException::classNotFound($controller);

        $classReflector = new ReflectionClass($controller);

        $routePrefix = $this->getAttributeInstance($classReflector, RoutePrefix::class);

        foreach ($classReflector->getMethods() as $method) {
            $route = $this->getAttributeInstance($method, Route::class);

            if (!$route) continue;

            $uri = $this->generateUri($route, $routePrefix, $method->getName());

            $this->routes[strtoupper($route->method)][] = [
                'uri' => $this->generateUriPattern($uri),
                'callback' => [$controller, $method->getName()],
                'middlewares' => $route->middlewares,
            ];
        }
    }

    private function getAttributeInstance(object $reflector, string $attributeClass): ?object
    {
        $attributes = $reflector->getAttributes($attributeClass, ReflectionAttribute::IS_INSTANCEOF);

        return $attributes ? $attributes[0]->newInstance() : null;
    }

    private function generateUri(object $route, ?object $routePrefix, string $methodName): string
    {
        $uri = $route->uri;

        if ($routePrefix && $this->shouldApplyPrefix($routePrefix, $methodName)) {
            $uri = $this->generatePrefixUri($routePrefix->prefix, $uri);
        }

        return $uri;
    }

    private function shouldApplyPrefix(RoutePrefix $routePrefix, string $methodName): bool
    {
        return empty($routePrefix->only) && empty($routePrefix->except) ||
            (!empty($routePrefix->only) && in_array($methodName, $routePrefix->only, true)) ||
            (!empty($routePrefix->except) && !in_array($methodName, $routePrefix->except, true));
    }

    private function generatePrefixUri(string $prefix, string $uri): string
    {
        $trimmedPrefix = trim($prefix, '/');
        $trimmedUri = trim($uri, '/') ?: '/';

        return ($trimmedUri !== '/') ?
            sprintf('/%s/%s', $trimmedPrefix, $trimmedUri) :
            sprintf('/%s', $trimmedPrefix);
    }

    private function generateUriPattern(string $uri): string
    {
        return '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . '$#';
    }
}
