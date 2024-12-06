<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

class RouteMatcher 
{
    public function __construct(
        private string $method,
        private string $uri,
    ){}

    
}
