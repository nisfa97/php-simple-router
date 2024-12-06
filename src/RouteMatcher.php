<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\RouteMatcherException;

class RouteMatcher 
{
    public function __construct(
        private string  $method,
        private string  $uri,
        private array   $routeCollection,
        private array   $objectToIgnore = []
    ){}

    public function match(): string
    {
        if (! $this->routeCollection) {
            throw RouteMatcherException::routeCollectionEmpty();
        }

        if (! array_key_exists($this->method, $this->routeCollection)) {
            throw RouteMatcherException::requestMethodNotRegistered($this->method);
        }

        foreach ($this->routeCollection[$this->method] as $route) {
            if (preg_match($route['uri'], $this->uri, $matches)) {
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$class, $method] = $route['callback'];

                if (! class_exists($class)) {
                    throw RouteMatcherException::classNotFound($class);
                }

                if (! method_exists($class, $method)) {
                    throw RouteMatcherException::methodNotFound($class, $method);
                }

                $res = (new $class())->$method();

                return $this->ensureString($res);
            }
        }

        throw RouteMatcherException::routeNotFound();
    }

    private function ensureString($value): string
    {
        foreach ($this->objectToIgnore as $object) {
            if ($value instanceof $object) {
               if (method_exists($value, '__toString')) {
                return (string) $value;
               }

               throw RouteMatcherException::objectNotImplementToStringMethod($value);
            }
        }

        if (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
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
