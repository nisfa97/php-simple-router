<?php

declare (strict_types = 1);

namespace Nisfa97\PhpSimpleRouter\Routing;

use Nisfa97\PhpSimpleRouter\Container;
use Nisfa97\PhpSimpleRouter\Exceptions\RouteMatcherException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;

class RouteMatcher
{
    public function __construct(
        private string $method,
        private string $uri,
        private ?RouteCollection $routeCollection,
        private ?RouteMiddleware $middleware,
        private ?Container $container,
    ) {}

    public function match(): string
    {
        $routes = $this->routeCollection->getRoutes();

        if (!array_key_exists($this->method, $routes)) {
            throw RouteMatcherException::requestMethodNotRegistered($this->method);
        }

        foreach ($routes[$this->method] as $route) {
            $routeParams = $this->isRouteMatched($route);

            if (is_array($routeParams)) {
                return $this->resolveRoute($route, $routeParams);
            }
        }

        throw RouteMatcherException::routeNotFound();
    }

    private function isRouteMatched(array $route): array | false
    {
        $res = preg_match($route['uri'], $this->uri, $matches);

        if ($res) {
            return (!empty($matches))
            ? array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)
            : [];
        }

        return false;
    }

    private function resolveRoute(array $route, array $routeParams): string
    {
        [$class, $method] = $route['callback'];

        $instance = $this->container->get($class);

        $dependencies = $this->getMethodDependencies($instance, $method, $routeParams);

        $callback = ($dependencies)
        ? fn() => $instance->$method(...$dependencies)
        : fn() => $instance->$method();

        $response = $this->middleware->resolve($callback, $route['middlewares']);

        return $this->response($response);
    }

    private function getMethodDependencies(object $instance, string $method, array $routeParams): array
    {
        $methodReflector = new ReflectionMethod($instance, $method);

        $methodParameters = $methodReflector->getParameters();

        if (!$methodParameters) {
            return [];
        }

        $dependencies = array_map(function (ReflectionParameter $param) use ($routeParams) {
            return $this->resolveParameters($param, $routeParams);
        }, $methodParameters);

        return $dependencies;
    }

    private function resolveParameters(ReflectionParameter $param, array $routeParams): mixed
    {
        $name = $param->getName();
        $type = $param->getType();

        if (isset($routeParams[$name])) {
            return $routeParams[$name];
        }

        if (!$type) {
            throw RouteMatcherException::parameterHasNoTypeHint($param->getName());
        }

        if ($type instanceof ReflectionUnionType) {
            throw RouteMatcherException::parameterHasUnionType($param->getName());
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if (!$type->isBuiltin()) {
            return $this->container->get($type->getName());
        }

        throw RouteMatcherException::failedToResolveDependency($type, $param->getName());
    }

    private function response(callable $callback): string
    {
        ob_start();

        $response = $callback();

        echo $this->formatToString($response);

        return ob_get_clean();
    }

    private function formatToString(mixed $response): string
    {
        return match (gettype($response)) {
            'array' => json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'object' => method_exists($response, '__toString') ? (string) $response : print_r($response, true),
            'int', 'float', 'string', 'bool' => (string) $response,
            default => ''
        };
    }
}
