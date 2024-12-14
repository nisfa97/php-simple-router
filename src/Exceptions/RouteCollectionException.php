<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class RouteCollectionException extends \Exception
{
    public static function emptyPassedArgument()
    {
        return new self("The provided argument is empty. Please ensure a valid value is passed.");
    }

    public static function classNotFound(string $class): self
    {
        return new self("The specified class '$class' could not be found. Please ensure that the class name is correct and properly autoloaded.");
    }
}
