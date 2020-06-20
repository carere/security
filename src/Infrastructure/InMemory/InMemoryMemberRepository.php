<?php

namespace Addworking\Security\Infrastructure\InMemory;

use Addworking\Security\Domain\Models\Member;
use Addworking\Security\Domain\Repositories\MemberRepository;

class InMemoryMemberRepository implements MemberRepository
{
    private array $members = [];

    public function save(Member $member): void
    {
        if (!isset($this->members[$member->getId()])) {
            $this->members[$member->getId()] = $member;
        }
    }

    public function findByUserAndEnterprise(
        string $userId,
        string $enterpriseId
    ): ?Member {
        $members = array_filter(
            $this->members,
            fn(Member $m) => $m->getEnterprise()->getId() === $enterpriseId &&
                $m->getUser()->getId() === $userId
        );

        return !empty($members) ? current($members) : null;
    }
}
