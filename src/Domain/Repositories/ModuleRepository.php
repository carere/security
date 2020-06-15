<?php

namespace Addworking\Security\Domain\Repositories;

use Addworking\Security\Domain\Models\Module;

interface ModuleRepository
{
    public function nextIdentity(): string;
    public function find(string $id): ?Module;
    public function add(Module $module): void;
    public function findByName(string $name): ?Module;
    public function delete(string $id): bool;
    public function save(Module $module): void;
}
