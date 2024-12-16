<?php

declare (strict_types = 1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class MiddlewareException extends \Exception
{
    public static function handleMethodNotFound(string $middleware): self
    {
        return new self("Middleware '$middleware' does not have handle method.");
    }
}
