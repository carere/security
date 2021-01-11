<?php

namespace Ashiso\Security\Infrastructure\InMemory;

use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;

class InMemoryAuthenticationGateway implements AuthenticationGateway
{
    private ?User $connectedUser = null;

    public function authenticate(User $user): void
    {
        $this->connectedUser = $user;
    }

    public function connectedUser(): ?User
    {
        return $this->connectedUser;
    }
}
