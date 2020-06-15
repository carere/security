<?php

namespace Addworking\Security\Infrastructure\InMemory;

use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Repositories\ModuleRepository;

class InMemoryModuleRepository implements ModuleRepository
{
    private array $modules = [];

    public function add(Module $module): void
    {
        $this->modules[$module->getId()] = $module;
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

    public function delete(string $id): bool
    {
        $canRemove = array_key_exists($id, $this->modules);

        if ($canRemove) {
            unset($this->modules[$id]);
        }

        return $canRemove;
    }

    public function save(Module $module): void
    {
        //TODO: no need to save when using in memory with PHP :)
    }
}
