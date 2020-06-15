<?php

namespace Addworking\Security\Application\Services;

use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Domain\Repositories\MemberRepository;

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
                $this->enterpriseRepository->findAddworking()->getId()
            );
    }
}
