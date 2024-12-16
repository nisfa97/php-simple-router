<?php

declare (strict_types = 1);

namespace Nisfa97\PhpSimpleRouter\Routing;

use Nisfa97\PhpSimpleRouter\Exceptions\MiddlewareException;

class RouteMiddleware
{
    private array $middlewares = ['*' => []];

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(string | array $controllers): void
    {
        if (is_string($controllers)) {
            $this->addMiddleware('*', $controllers);
            return;
        }

        foreach ($controllers as $key => $controller) {
            $key = $this->normalizeMiddlewareKey($key);

            if (is_string($controller)) {
                $this->addMiddleware($key, $controller);
            } else {
                foreach ($controller as $c) {
                    $this->addMiddleware($key, $c);
                }
            }
        }
    }

    private function addMiddleware(string $key, string $value): void
    {
        $this->middlewares[$key] ??= [];

        if (!in_array($value, $this->middlewares[$key], true)) {
            $this->middlewares[$key][] = $value;
        }
    }

    private function normalizeMiddlewareKey(string | int $name): string
    {
        return is_int($name) ? '*' : $name;
    }

    public function resolve(callable $next, array $routeMiddlewares): callable
    {
        foreach (array_reverse($this->middlewares['*']) as $global) {
            if (!method_exists($global, 'handle')) {
                throw MiddlewareException::handleMethodNotFound($global);
            }

            $next = (new $global())->handle($next);
        }

        if (!$routeMiddlewares) {
            return $next;
        }

        foreach ($routeMiddlewares as $alias) {
            if (isset($this->middlewares[$alias])) {
                foreach (array_reverse($this->middlewares[$alias]) as $middleware) {
                    if (!method_exists($middleware, 'handle')) {
                        throw MiddlewareException::handleMethodNotFound($global);
                    }

                    $next = (new $middleware())->handle($next);
                }
            }
        }

        return $next;
    }
}
