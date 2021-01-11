<?php

namespace Ashiso\Security\Domain\Repositories;

use Ashiso\Security\Domain\Models\Module;

interface ModuleRepository
{
    public function nextIdentity(): string;
    public function find(string $id): ?Module;
    public function save(Module $module): void;
    public function findByName(string $name): ?Module;
    public function delete(Module $module): void;
}
