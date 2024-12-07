<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

class Middleware
{
    private array $middlewares = ['*' => []];

    public function addMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middlewareKey => $middleware) {
            $key = $this->checkIsKeyGlobal($middlewareKey);

            if (is_string($middleware)) {
                if (!isset($this->middlewares[$key][$middleware])) {
                    $this->middlewares[$key][] = $middleware;
                }
            } else {
                foreach ($middleware as $middlewareArrayValue) {
                    if (!isset($this->middlewares[$key][$middlewareArrayValue])) {
                        $this->middlewares[$key][] = $middlewareArrayValue;
                    }
                }
            }
        }
    }

    private function checkIsKeyGlobal(string|int $key): string
    {
        if ($key === '*' || is_int($key)) {
            return '*';
        }

        return $key;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
