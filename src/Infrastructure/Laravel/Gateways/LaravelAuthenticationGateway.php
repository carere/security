<?php

namespace Ashiso\Security\Infrastructure\Laravel\Gateways;

use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\UserRepository;
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
