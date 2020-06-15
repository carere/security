<?php

namespace Addworking\Security\Domain\Gateways;

use Addworking\Security\Domain\Models\User;

interface AuthenticationGateway
{
    public function authenticate(User $user): void;
    public function connectedUser(): ?User;
}
