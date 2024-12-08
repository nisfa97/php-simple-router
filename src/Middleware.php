<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

class Middleware
{
    private array $middlewares = ['*' => []];

    public function registerMiddlewares(string|array $middlewares): void
    {
        if (is_string($middlewares)) {
            $this->addMiddleware('*', $middlewares);
            return;
        }

        foreach ($middlewares as $middlewareKey => $middlewareClasses) {
            $key = $this->normalizeKey($middlewareKey);

            if (is_string($middlewareClasses)) {
                $this->addMiddleware($key, $middlewareClasses);
                continue;
            }

            foreach ($middlewareClasses as $middlewareClass) {
                $this->addMiddleware($key, $middlewareClass);
            }
        }
    }

    private function addMiddleware(string $key, string $middleware): void
    {
        if (! isset($this->middlewares[$key])) {
            $this->middlewares[$key] = [];
        }

        if (! in_array($middleware, $this->middlewares[$key], true)) {
            $this->middlewares[$key][] = $middleware;
        }

        // if (! array_key_exists($middleware, array_flip($this->middlewares[$key]))) {
        //     $this->middlewares[$key][] = $middleware;
        // }
    }

    private function normalizeKey(string|int $key): string
    {
        return (is_int($key)) ? '*' : $key;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
