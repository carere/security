<?php

namespace Ashiso\Security\Application\Commands;

class RemoveModule
{
    private string $moduleId;

    public function __construct(string $moduleId)
    {
        $this->moduleId = $moduleId;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }
}
