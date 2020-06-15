<?php

namespace Addworking\Security\Domain\Models;

class User
{
    private string $id;
    private string $firstname;
    private string $lastname;

    public function __construct(string $id, string $firstname, string $lastname)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }
}
