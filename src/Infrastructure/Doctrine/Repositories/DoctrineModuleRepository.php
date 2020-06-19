<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Addworking\Security\Domain\Models\Module;
use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Repositories\ModuleRepository;

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
            ->createQuery("SELECT m FROM :class m WHERE m.name = :name")
            ->setParameters(['class' => Module::class, 'name' => $name])
            ->getResult();
    }

    public function add(Module $module): void
    {
        if (!$this->em->contains($module)) {
            $this->em->persist($module);
        }
    }
}