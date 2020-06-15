<?php

namespace Addworking\Security\Application\Commands;

class EditModule
{
    private string $id;
    private string $description;

    public function __construct(string $id, string $description)
    {
        $this->id = $id;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
