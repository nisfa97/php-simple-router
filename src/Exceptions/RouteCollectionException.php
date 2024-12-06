<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Exceptions;

class RouteCollectionException extends \Exception
{
    public static function classNotFound(string $class): self
    {
        return new self(sprintf("Class '%s' is not found.", $class));
    }
}
