<?php

namespace Ashiso\Security\Domain\Models;

class Member
{
    private string $id;
    private User $user;
    private Enterprise $enterprise;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setEnterprise(Enterprise $enterprise): self
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    public function getEnterprise(): Enterprise
    {
        return $this->enterprise;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
