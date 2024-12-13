<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Routing;

use Nisfa97\PhpSimpleRouter\Container;
use Nisfa97\PhpSimpleRouter\Exceptions\RouteMatcherException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;

class RouteMatcher
{
    public function __construct(
        private string              $method,
        private string              $uri,
        private ?RouteCollection    $routeCollection,
        private ?RouteMiddleware    $middleware,
        private ?Container          $container,
    ) {}

    public function match(): string
    {
        $routes = $this->routeCollection->getRoutes();

        if (! array_key_exists($this->method, $routes)) {
            throw RouteMatcherException::requestMethodNotRegistered($this->method);
        }

        foreach ($routes[$this->method] as $route) {
            if (preg_match($route['uri'], $this->uri, $matches)) {
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY) ?? [];

                $routeMiddlewares = $route['middlewares'] ?? [];

                [$class, $method] = $route['callback'];

                $instance = $this->container->get($class);

                $dependencies = $this->getMethodDependencies($instance, $method, $routeParams);

                if ($dependencies) {
                    $res = $this->middleware->resolve(fn() => $instance->$method(...$dependencies), $routeMiddlewares);
                } else {
                    $res = $this->middleware->resolve(fn() => $instance->$method(), $routeMiddlewares);
                }

                return $this->responseString($res());
            }
        }

        throw RouteMatcherException::routeNotFound();
    }

    private function getMethodDependencies(object $instance, string $method, array $routeParams): array
    {
        $methodReflector = new ReflectionMethod($instance, $method);

        $methodParameters = $methodReflector->getParameters();

        if (!$methodParameters) {
            return [];
        }

        $dependencies = array_map(function (ReflectionParameter $param) use ($routeParams) {
            $type = $param->getType();

            if (isset($routeParams[$param->getName()])) {
                return $routeParams[$param->getName()];
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
        }, $methodParameters);

        return $dependencies;
    }

    private function responseString($value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            ob_start();
            print_r($value);
            return ob_get_clean();
        }

        if (is_scalar($value) || is_null($value)) {
            return (string) $value;
        }

        return '[Unsupported Type: ' . gettype($value) . ']';
    }
}
