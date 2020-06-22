<?php

namespace Addworking\Security\Domain\Repositories;

use Addworking\Security\Domain\Models\User;

interface UserRepository
{
    public function save(User $user): void;
    public function find(string $id): ?User;
    public function findByName(string $name): ?User;
}
