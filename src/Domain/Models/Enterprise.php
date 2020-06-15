<?php

namespace Addworking\Security\Domain\Models;

class Enterprise
{
    private string $id;
    private string $name;
    private array $modules;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addModule(Module $module): self
    {
        $this->modules[$module->getId()] = $module;

        return $this;
    }

    public function getModules(): array
    {
        return $this->modules;
    }
}
