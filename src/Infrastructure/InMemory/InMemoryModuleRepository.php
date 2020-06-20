<?php

namespace Addworking\Security\Infrastructure\InMemory;

use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Repositories\ModuleRepository;

class InMemoryModuleRepository implements ModuleRepository
{
    private array $modules = [];

    public function save(Module $module): void
    {
        if (!isset($this->modules[$module->getId()])) {
            $this->modules[$module->getId()] = $module;
        }
    }

    public function findByName(string $name): ?Module
    {
        $modules = array_filter(
            $this->modules,
            fn(Module $m) => $m->getName() === $name
        );

        return !empty($modules) ? current($modules) : null;
    }

    public function find(string $id): ?Module
    {
        return $this->modules[$id] ?? null;
    }

    public function nextIdentity(): string
    {
        return str_shuffle("a1b2c3d4e5f6g7h8i9j0");
    }

    public function delete(Module $module): void
    {
        if (array_key_exists($module->getId(), $this->modules)) {
            unset($this->modules[$module->getId()]);
        }
    }
}
