<?php

namespace Addworking\Security\Domain\Repositories;

use Addworking\Security\Domain\Models\User;

interface UserRepository
{
    public function add(User $user): void;
    public function findByName(string $name): ?User;
}
