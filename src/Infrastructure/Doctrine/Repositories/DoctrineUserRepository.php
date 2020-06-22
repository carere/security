<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Repositories\UserRepository;

class DoctrineUserRepository implements UserRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function find(string $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    public function findByName(string $name): ?User
    {
        return $this->em
            ->createQuery(
                sprintf(
                    "SELECT u FROM %s u WHERE CONCAT(u.firstname, ' ', u.lastname) = :name",
                    User::class
                )
            )
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        if (!$this->em->contains($user)) {
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}
