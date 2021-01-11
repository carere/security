<?php

namespace Ashiso\Security\Application;

use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Domain\Repositories\MemberRepository;

class AuthorizationChecker
{
    private MemberRepository $memberRepository;
    private EnterpriseRepository $enterpriseRepository;

    public function __construct(
        MemberRepository $memberRepository,
        EnterpriseRepository $enterpriseRepository
    ) {
        $this->memberRepository = $memberRepository;
        $this->enterpriseRepository = $enterpriseRepository;
    }

    public function isSupport(User $user): bool
    {
        return null !==
            $this->memberRepository->findByUserAndEnterprise(
                $user->getId(),
                $this->enterpriseRepository->findAshiso()->getId()
            );
    }
}
