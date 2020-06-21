<?php

namespace Addworking\Security\Domain\Models;

use Doctrine\Common\Collections\ArrayCollection;

class Module
{
    private string $id;
    private string $name;
    private string $description;
    private \ArrayAccess $childrens;
    private Module $parent;

    public function __construct(string $id, string $name, string $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->childrens = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function addChild(Module $child): self
    {
        $this->childrens[$child->getId()] = $child;

        return $this;
    }

    public function setParent(Module $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChildrens(): \ArrayAccess
    {
        return $this->childrens;
    }
}
