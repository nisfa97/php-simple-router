<?php

namespace Nisfa97\PhpSimpleRouter\Utils;

use Nisfa97\PhpSimpleRouter\Http\Response;

function dd(...$vals): void
{
    echo '<pre>';
    var_dump(...$vals);
    echo '</pre>';
}

function redirect(string $path, int $code): void
{
    (new Response())->redirect($path, $code);
}
