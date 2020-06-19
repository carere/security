<?php

namespace Addworking\Security\Infrastructure\Doctrine\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Addworking\Security\Domain\Models\Member;
use Addworking\Security\Domain\Repositories\MemberRepository;

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
                "SELECT m FROM :class m WHERE m.user_id = :user_id AND m.enterprise_id = :enterprise_id"
            )
            ->setParameters([
                'class' => Member::class,
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
            ])
            ->getResult();
    }

    public function add(Member $member): void
    {
        if (!$this->em->contains($member)) {
            $this->em->persist($member);
        }
    }
}
