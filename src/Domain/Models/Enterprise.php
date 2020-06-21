<?php

namespace Addworking\Security\Domain\Models;

use Doctrine\Common\Collections\ArrayCollection;

class Enterprise
{
    private string $id;
    private string $name;
    private ArrayCollection $modules;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->modules = new ArrayCollection();
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
        $this->modules->add($module);

        return $this;
    }

    public function getModules(): ArrayCollection
    {
        return $this->modules;
    }
}
