<?php

namespace Addworking\Security\Domain\Repositories;

use Addworking\Security\Domain\Models\Member;

interface MemberRepository
{
    public function add(Member $member): void;
    public function findByUserAndEnterprise(
        string $userId,
        string $enterpriseId
    ): ?Member;
}
