<?php

namespace Addworking\Security\Infrastructure\InMemory;

use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;

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
