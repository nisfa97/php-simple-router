<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RoutePrefix
{
    public function __construct(
        public readonly string $prefix,
        public readonly array $only = [],
        public readonly array $except = [],
    ) {
    }
}