<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string  $method,
        public readonly string  $uri,
        public readonly array   $middlewares = [],
    ) {}
}