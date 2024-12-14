<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Attributes\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    const METHOD_GET        = 'GET';
    const METHOD_POST       = 'POST';
    const METHOD_PUT        = 'PUT';
    const METHOD_PATCH      = 'PATCH';
    const METHOD_DELETE     = 'DELETE';
    const METHOD_OPTIONS    = 'OPTIONS';

    public function __construct(
        public readonly string  $method,
        public readonly string  $uri,
        public readonly array   $middlewares = [],
    ) {}
}