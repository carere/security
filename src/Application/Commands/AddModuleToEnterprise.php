<?php

namespace Ashiso\Security\Application\Commands;

class AddModuleToEnterprise
{
    private string $moduleId;
    private string $enterpriseId;

    public function __construct(string $moduleId, string $enterpriseId)
    {
        $this->moduleId = $moduleId;
        $this->enterpriseId = $enterpriseId;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
    }
}
