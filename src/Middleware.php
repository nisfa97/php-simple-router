<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Closure;

class Middleware
{
    private array $middlewares = ['*' => []];

    public function setMiddlewares(string|array $controllers): void
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

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    private function addMiddleware(string $key, string $value): void
    {
        if (! isset($this->middlewares[$key])) {
            $this->middlewares[$key] = [];
        }

        if (empty($this->middlewares[$key])) {
            $this->middlewares[$key][] = $value;
            return;
        }

        foreach ($this->middlewares[$key] as $middleware) {
            if ($middleware !== $value) {
                $this->middlewares[$key][] = $value;
            }
        }
    }

    private function normalizeMiddlewareKey(string|int $name): string
    {
        return is_int($name) ? '*' : $name;
    }

    public function resolve(array $key, Closure $next): void
    {
        
    }
}
