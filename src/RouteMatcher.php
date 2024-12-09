<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteMatcherException;

class RouteMatcher
{
    public function __construct(
        private string              $method,
        private string              $uri,
        private ?RouteCollection    $routeCollection,
        private array               $objectToIgnore = []
    ) {}

    public function match(): string
    {
        $routes         = $this->routeCollection->getRoutes();
        $middlewares    = $this->routeCollection->getMiddlewares();

        if (! array_key_exists($this->method, $routes)) {
            throw RouteMatcherException::requestMethodNotRegistered($this->method);
        }

        foreach ($routes[$this->method] as $route) {
            if (preg_match($route['uri'], $this->uri, $matches)) {
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $method] = $route['callback'];

                // check for global middlewares

                // check if route has middlewares

                $res = (new $class())->$method();

                return $this->ensureString($res);
            }
        }

        throw RouteMatcherException::routeNotFound();
    }

    private function ensureString($value): string
    {
        if ($this->objectToIgnore) {
            foreach ($this->objectToIgnore as $object) {
                if ($value instanceof $object) {
                    if (method_exists($value, '__toString')) {
                        return (string) $value;
                    }

                    throw RouteMatcherException::objectNotImplementToStringMethod($value);
                }
            }
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (is_object($value)) {
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
