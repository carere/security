<?php

namespace Addworking\Security\Domain\Repositories;

use Addworking\Security\Domain\Models\Enterprise;

interface EnterpriseRepository
{
    public function add(Enterprise $enterprise): void;
    public function findByName(string $name): ?Enterprise;
    public function findAddworking(): ?Enterprise;
    public function findByModuleId(string $moduleId): array;
}
