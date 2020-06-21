<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Addworking\Security\Domain\Models\Enterprise;
use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;

class DoctrineEnterpriseRepository implements EnterpriseRepository
{
    const ADDWORKING_ID = "f1494810-ed7a-406f-8aeb-7845c4105b01";

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findByName(string $name): ?Enterprise
    {
        return $this->em
            ->createQuery(
                sprintf(
                    "SELECT e FROM %s e WHERE e.name = :name",
                    Enterprise::class
                )
            )
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function findAddworking(): ?Enterprise
    {
        return $this->em->find(Enterprise::class, self::ADDWORKING_ID);
    }

    public function findByModuleId(string $moduleId): array
    {
        return $this->em
            ->createQuery(
                sprintf(
                    "SELECT e FROM %s e JOIN e.modules m WHERE m.id = :module_id",
                    Enterprise::class
                )
            )
            ->setParameter('module_id', $moduleId)
            ->getResult();
    }

    public function save(Enterprise $enterprise): void
    {
        if (!$this->em->contains($enterprise)) {
            $this->em->persist($enterprise);
        }

        $this->em->flush();
    }
}
