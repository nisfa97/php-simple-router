<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

use ReflectionType;

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
        return new self("Object of type '" . get_class($class) . "'does not implement __toString().");
    }

    public static function parameterHasNoTypeHint(string $paramName): self
    {
        return new self("Unable to resolve the parameter '$paramName' because it lacks a type hint. Please ensure all parameters have explicit type hints.");
    }

    public static function parameterHasUnionType(string $paramName): self
    {
        return new self("The parameter '$paramName' cannot be resolved because it uses a Union Type, which is not supported. Consider using a single, specific type.");
    }

    public static function failedToResolveDependency(ReflectionType $paramType, string $paramName): self
    {
        return new self("Failed to resolve the parameter '$paramName' with the type hint '$paramType'. Ensure the dependency is correctly bound or available in the container.");
    }
}
