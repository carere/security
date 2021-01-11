<?php

namespace Ashiso\Security\Domain\Gateways;

use Ashiso\Security\Domain\Models\User;

interface AuthenticationGateway
{
    public function authenticate(User $user): void;
    public function connectedUser(): ?User;
}
