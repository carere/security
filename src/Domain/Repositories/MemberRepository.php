<?php

namespace Ashiso\Security\Domain\Repositories;

use Ashiso\Security\Domain\Models\Member;

interface MemberRepository
{
    public function save(Member $member): void;
    public function findByUserAndEnterprise(
        string $userId,
        string $enterpriseId
    ): ?Member;
}
