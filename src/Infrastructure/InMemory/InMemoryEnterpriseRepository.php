<?php

namespace Ashiso\Security\Infrastructure\InMemory;

use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Domain\Models\Enterprise;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;

class InMemoryEnterpriseRepository implements EnterpriseRepository
{
    private array $enterprises = [];

    public function save(Enterprise $enterprise): void
    {
        if (!isset($this->enterprises[$enterprise->getId()])) {
            $this->enterprises[$enterprise->getId()] = $enterprise;
        }
    }

    public function find(string $id): ?Enterprise
    {
        return $this->enterprises[$id] ?? null;
    }

    public function findByName(string $name): ?Enterprise
    {
        $enterprises = array_filter(
            $this->enterprises,
            fn(Enterprise $e) => $e->getName() === $name
        );

        return !empty($enterprises) ? current($enterprises) : null;
    }

    public function findAshiso(): ?Enterprise
    {
        return $this->findByName('Ashiso');
    }

    public function findByModuleId(string $moduleId): array
    {
        return array_filter(
            $this->enterprises,
            fn(Enterprise $e) => $e
                ->getModules()
                ->exists(fn(int $key, Module $m) => $m->getId() === $moduleId)
        );
    }
}
