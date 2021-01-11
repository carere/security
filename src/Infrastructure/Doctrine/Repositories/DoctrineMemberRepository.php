<?php

namespace Ashiso\Security\Infrastructure\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Ashiso\Security\Domain\Models\Member;
use Ashiso\Security\Domain\Repositories\MemberRepository;

class DoctrineMemberRepository implements MemberRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findByUserAndEnterprise(
        string $userId,
        string $enterpriseId
    ): ?Member {
        return $this->em
            ->createQuery(
                sprintf(
                    "SELECT m FROM %s m WHERE m.user = :user_id AND m.enterprise = :enterprise_id",
                    Member::class
                )
            )
            ->setParameters([
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
            ])
            ->getOneOrNullResult();
    }

    public function save(Member $member): void
    {
        if (!$this->em->contains($member)) {
            $this->em->persist($member);
        }

        $this->em->flush();
    }
}
