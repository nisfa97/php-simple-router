<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Routing\Container;
use Nisfa97\PhpSimpleRouter\Routing\RouteCollection;
use Nisfa97\PhpSimpleRouter\Routing\RouteMatcher;
use Nisfa97\PhpSimpleRouter\Routing\RouteMiddleware;

class Router
{
    public function __construct(
        private string              $requestMethod  = '',
        private string              $requestUri     = '',
        private ?RouteCollection    $collection     = null,
        private ?RouteMiddleware    $middleware     = null,
        private ?RouteMatcher       $routeMatcher   = null,
        private ?Container          $container      = null
    ) {
        $this->requestMethod    = $_SERVER['REQUEST_METHOD'];
        $this->requestUri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (! $collection) {
            $this->collection = new RouteCollection();
        }

        if (! $middleware) {
            $this->middleware = new RouteMiddleware();
        }

        if (! $routeMatcher) {
            $this->routeMatcher = new RouteMatcher(
                $this->requestMethod,
                $this->requestUri,
                $this->collection,
                $this->middleware,
            );
        }

        if (! $container) {
            $this->container = new Container();
        }
    }

    public function registerControllers(string|array $controllers): Router
    {
        $this->collection->setControllers($controllers);
        return $this;
    }

    public function registerMiddlewares(string|array $middlewares): Router
    {
        $this->middleware->setMiddlewares($middlewares);
        return $this;
    }

    public function getRoutes(): array
    {
        return $this->collection->getRoutes();
    }

    public function getMiddlewares(): array
    {
        return $this->middleware->getMiddlewares();
    }

    public function getAll(): array
    {
        return [
            'routes' => $this->collection->getRoutes(),
            'middlewares' => $this->middleware->getMiddlewares()
        ];
    }

    public function match(): string
    {
        return $this->routeMatcher->match();
    }
}