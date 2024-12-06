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
    ){}

    public function match()
    {
        if (! $this->method) {
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

                return (new $class())->$method();
            }
        }

        throw RouteMatcherException::routeNotFound();
    }
}
