<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class RouteCollectionException extends \Exception
{
    public static function emptyController()
    {
        return new self("Controller name cannot be empty.");
    }

    public static function classNotFound(string $class): self
    {
        return new self("The specified class '$class' could not be found. Please ensure that the class name is correct and properly autoloaded.");
    }
}
