<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Repositories\UserRepository;
use Doctrine\ORM\AbstractQuery;

class DoctrineUserRepository implements UserRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findByName(string $name): ?User
    {
        return $this->em
            ->createQuery(
                "SELECT u FROM Addworking\Security\Domain\Models\User u WHERE CONCAT(u.firstname, u.lastname) = :name"
            )
            ->setParameter('name', $name)
            ->getResult(AbstractQuery::HYDRATE_OBJECT);
    }

    public function save(User $user): void
    {
        if (!$this->em->contains($user)) {
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}
