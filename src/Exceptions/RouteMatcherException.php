<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class RouteMatcherException extends \Exception
{
    public static function routeCollectionEmpty(): self
    {
        return new self("The route collection is empty. Ensure routes are registered before attempting to match them.");
    }

    public static function requestMethodNotRegistered(string $method): self
    {
        return new self("The HTTP method '$method' is not supported. Verify that the method is registered in the route collection.");
    }

    public static function classNotFound(string $class): self
    {
        return new self("The controller class '$class' could not be found. Ensure the class name and namespace are correct, and it is properly autoloaded.");
    }

    public static function methodNotFound(string $class, string $method): self
    {
        return new self("The method '$method' was not found in the controller class '$class'. Check if the method exists and is accessible.");
    }

    public static function routeNotFound(): self
    {
        return new self("No route matched the current request. Verify the requested URI and ensure corresponding routes are registered.");
    }

    public static function objectNotImplementToStringMethod(object $class): self
    {
        return new self('Object of type ' . get_class($class) . 'does not implement __toString().');
    }
}