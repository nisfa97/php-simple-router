<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter\Routing;

use Nisfa97\PhpSimpleRouter\Exceptions\ContainerException;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;

class Container
{
    private array $instances    = [];
    private array $bindings     = [];

    public function bind(string $id, callable $callback): void
    {
        $this->bindings[$id] = $callback;
    }

    public function singleton(string $id, callable $callback): void
    {
        $this->instances[$id] = $callback();
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]) || class_exists($id);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            return $this->bindings[$id]();
        }

        if (class_exists($id)) {
            return $this->resolveClass($id);
        }

        throw ContainerException::failedToRetrieveId($id);
    }

    public function resolveClass(string $id): object
    {
        $classReflector = new ReflectionClass($id);

        $constructor            = $classReflector->getConstructor();
        $constructorParameters  = $constructor->getParameters();

        if ($constructor === null || $constructorParameters === []) {
            $this->bindings[$id] = fn(): object => $classReflector->newInstance();
            return $this->bindings[$id]();
        }

        $dependencies = array_map(fn(ReflectionParameter $param) => $this->resolveParameter($param), $constructorParameters);

        $this->bindings[$id] = fn(): object => $classReflector->newInstanceArgs($dependencies);
        return $this->bindings[$id]();
    }

    public function resolveParameter(ReflectionParameter $param)
    {
        $type = $param->getType();

        if (!$type) {
            throw ContainerException::parameterHasNoTypeHint($param->getName());
        }

        if ($type instanceof ReflectionUnionType) {
            throw ContainerException::parameterHasUnionType($param->getName());
        }

        if ($type && $type->allowsNull() && !$param->isDefaultValueAvailable()) {
            return null;
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($type && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        throw ContainerException::failedToResolveDependency($type, $param->getName());
    }
}
