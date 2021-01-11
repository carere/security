<?php

namespace Ashiso\Security\Domain\Repositories;

use Ashiso\Security\Domain\Models\Enterprise;

interface EnterpriseRepository
{
    public function save(Enterprise $enterprise): void;
    public function find(string $id): ?Enterprise;
    public function findByName(string $name): ?Enterprise;
    public function findAshiso(): ?Enterprise;
    public function findByModuleId(string $moduleId): array;
}
