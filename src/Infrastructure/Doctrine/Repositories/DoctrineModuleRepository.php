<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Addworking\Security\Domain\Models\Module;
use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Ramsey\Uuid\Uuid;

class DoctrineModuleRepository implements ModuleRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function find(string $id): ?Module
    {
        return $this->em->find(Module::class, $id);
    }

    public function findByName(string $name): ?Module
    {
        return $this->em
            ->createQuery(
                sprintf(
                    "SELECT m FROM %s m WHERE m.name = :name",
                    Module::class
                )
            )
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function delete(Module $module): void
    {
        $this->em->remove($module);
        $this->em->flush();
    }

    public function nextIdentity(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function save(Module $module): void
    {
        if (!$this->em->contains($module)) {
            $this->em->persist($module);
        }

        $this->em->flush();
    }
}
