<?php

namespace Ashiso\Security\Domain\Repositories;

use Ashiso\Security\Domain\Models\User;

interface UserRepository
{
    public function save(User $user): void;
    public function find(string $id): ?User;
    public function findByName(string $name): ?User;
}
