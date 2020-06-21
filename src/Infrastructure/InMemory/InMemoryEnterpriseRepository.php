<?php

namespace Addworking\Security\Infrastructure\InMemory;

use Addworking\Security\Domain\Models\Enterprise;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;

class InMemoryEnterpriseRepository implements EnterpriseRepository
{
    private array $enterprises = [];

    public function save(Enterprise $enterprise): void
    {
        if (!isset($this->enterprises[$enterprise->getId()])) {
            $this->enterprises[$enterprise->getId()] = $enterprise;
        }
    }

    public function findByName(string $name): ?Enterprise
    {
        $enterprises = array_filter(
            $this->enterprises,
            fn(Enterprise $e) => $e->getName() === $name
        );

        return !empty($enterprises) ? current($enterprises) : null;
    }

    public function findAddworking(): ?Enterprise
    {
        return $this->findByName('Addworking');
    }

    public function findByModuleId(string $moduleId): array
    {
        return array_filter(
            $this->enterprises,
            fn(Enterprise $e) => array_key_exists(
                $moduleId,
                $e->getModules()->toArray()
            )
        );
    }
}
