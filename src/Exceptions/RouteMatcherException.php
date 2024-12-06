<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class RouteMatcherException extends \Exception
{
    public static function routeCollectionEmpty(): self
    {
        return new self("Route collection is empty.");
    }

    public static function requestMethodNotRegistered(string $method): self
    {
        return new self("Method '$method' is not registered.");
    }

    public static function classNotFound(string $class): self
    {
        return new self("Class '$class' not found.");
    }

    public static function methodNotFound(string $class, string $method): self
    {
        return new self("Method '$method' not found in Class '$class'.");
    }

    public static function routeNotFound(): self
    {
        return new self("Not found.");
    }
}