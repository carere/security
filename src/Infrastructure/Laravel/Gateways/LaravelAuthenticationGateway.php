<?php

namespace Addworking\Security\Infrastructure\Laravel\Gateways;

use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class LaravelAuthenticationGateway implements AuthenticationGateway
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(User $user): void
    {
        // No need to implement here, because authentication is made by Laravel actually
    }

    public function connectedUser(): ?User
    {
        return $this->userRepository->find(Auth::id());
    }
}
