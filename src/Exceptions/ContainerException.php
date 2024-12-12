<?php

namespace Nisfa97\PhpSimpleRouter\Exceptions;

use ReflectionParameter;
use ReflectionType;

class ContainerException extends \Exception
{
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

    public static function failedToRetrieveId(string $id): self
    {
        return new self("The container was unable to retrieve the entry for identifier '$id'. Ensure the identifier is correctly registered and accessible.");
    }
}
