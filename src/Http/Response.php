<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Http;

class Response
{
    private int     $statusCode = 200;
    private array   $headers    = [];
    private string  $body       = '';

    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $key, string $value): Response
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setBody(string $body): Response
    {
        $this->body = $body;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        if (! empty($this->headers)) {
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }
        echo $this->body;
    }

    public function redirect(string $path, int $status = 302): void
    {
        header("Location: $path", response_code: $status);
        exit();
    }

    public function __tostring(): string
    {
        return $this->body;
    }
}
