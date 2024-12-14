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
        if (is_string($controllers)) {
            $this->addController($controllers);
            return;
        }

        foreach ($controllers as $controller) {
            $this->addController($controller);
        }
    }

    private function addController(string $controller): void
    {
        if (empty($controller)) {
            throw RouteCollectionException::emptyPassedArgument();
        }

        if (!class_exists($controller)) {
            throw RouteCollectionException::classNotFound($controller);
        }

        $classReflector = new ReflectionClass($controller);

        $classAttribute = $classReflector->getAttributes(RoutePrefix::class, ReflectionAttribute::IS_INSTANCEOF);

        $routePrefix = $classAttribute ? $classAttribute[0]->newInstance() : null;

        foreach ($classReflector->getMethods() as $method) {
            $methodAttribute = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);

            $route = $methodAttribute[0]->newInstance();

            $uri = $route->uri;

            if ($routePrefix) {
                if (empty($routePrefix->only) && empty($routePrefix->except)) {
                    $uri = $this->generatePrefixUri($routePrefix->prefix, $uri);
                }

                if (!empty($routePrefix->only) && in_array($method->getName(), $routePrefix->only, true)) {
                    $uri = $this->generatePrefixUri($routePrefix->prefix, $uri);
                }

                if (!empty($routePrefix->except) && !in_array($method->getName(), $routePrefix->except, true)) {
                    $uri = $this->generatePrefixUri($routePrefix->prefix, $uri);
                }
            }

            $this->routes[strtoupper($route->method)][] = [
                'uri' => $this->generateUriPattern($uri),
                'callback' => [$controller, $method->getName()],
                'middlewares' => $route->middlewares,
            ];
        }
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
