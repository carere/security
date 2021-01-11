<?php

namespace Ashiso\Security\Infrastructure\InMemory;

use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Repositories\UserRepository;

class InMemoryUserRepository implements UserRepository
{
    private array $users = [];

    public function save(User $user): void
    {
        if (!isset($this->users[$user->getId()])) {
            $this->users[$user->getID()] = $user;
        }
    }

    public function find(string $id): ?User
    {
        return isset($this->users[$id]) ?? null;
    }

    public function findByName(string $name): ?User
    {
        $users = array_filter(
            $this->users,
            fn(User $u) => $name === "{$u->getFirstname()} {$u->getLastname()}"
        );

        return !empty($users) ? current($users) : null;
    }
}
