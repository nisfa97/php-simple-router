<?php

declare(strict_types=1);

namespace Nisfa97\PhpSimpleRouter;

use Nisfa97\PhpSimpleRouter\Exceptions\ContainerException;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;

class Container
{
    private array $instances = [];
    private array $bindings = [];

    public function bind(string $id, callable $callback): void
    {
        $this->bindings[$id] = $callback;
    }

    public function singleton(string $id, callable $callback): void
    {
        $this->instances[$id] = null;
        $this->bindings[$id] = $callback;
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]) || class_exists($id);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            if ($this->instances[$id] === null) {
                $this->instances[$id] = $this->bindings[$id]($this);
            }

            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            return $this->bindings[$id]($this);
        }

        if (class_exists($id)) {
            return $this->resolveClass($id);
        }

        throw ContainerException::failedToRetrieveId($id);
    }

    public function resolveClass(string $id): object
    {
        $classReflector = new ReflectionClass($id);

        if (!$classReflector->isInstantiable()) {
            throw new \Exception('Failed because class is not instantiable.');
        }

        $constructor = $classReflector->getConstructor();
        if (!$constructor) {
            return $classReflector->newInstance();
        }

        $constructorParameters = $constructor->getParameters();
        if (!$constructorParameters) {
            return $classReflector->newInstance();
        }

        $dependencies = array_map(fn(ReflectionParameter $param) => $this->resolveParameter($param), $constructorParameters);

        return $classReflector->newInstanceArgs($dependencies);
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

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if (!$type->isBuiltin()) {
            return $this->get($type->getName());
        }

        throw ContainerException::failedToResolveDependency($type, $param->getName());
    }
}
