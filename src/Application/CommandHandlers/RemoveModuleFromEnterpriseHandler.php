<?php

namespace Addworking\Security\Application\CommandHandlers;

use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Models\Enterprise;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\Commands\RemoveModuleFromEnterprise;
use Addworking\Security\Domain\Exceptions\EnterpriseDoesntHaveTheModule;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;

class RemoveModuleFromEnterpriseHandler
{
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;

    public function __construct(
        ModuleRepository $moduleRepository,
        EnterpriseRepository $enterpriseRepository,
        AuthenticationGateway $authenticationGateway,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->enterpriseRepository = $enterpriseRepository;
        $this->authenticationGateway = $authenticationGateway;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function handle(RemoveModuleFromEnterprise $command)
    {
        $this->cancelIfUserNotAdmin();

        $enterprise = $this->enterpriseRepository->find(
            $command->getEnterpriseId()
        );

        $this->cancelIfEnterpriseDoesNotExist($enterprise);

        $module = $this->moduleRepository->find($command->getModuleId());

        $this->cancelIfModuleDoesNotExist($module);

        $this->cancelIfEnterpriseDoesntOwnTheModule($enterprise, $module);

        $this->removeMdouleFromEnterprise($module, $enterprise);
    }

    private function cancelIfUserNotAdmin()
    {
        $authenticatedUser = $this->authenticationGateway->connectedUser();

        if (!$this->authorizationChecker->isSupport($authenticatedUser)) {
            throw new MemberNotAdmin();
        }
    }

    private function cancelIfEnterpriseDoesNotExist(?Enterprise $enterprise)
    {
        if (null === $enterprise) {
            throw new EnterpriseDoesntExist();
        }
    }

    private function cancelIfModuleDoesNotExist(?Module $module)
    {
        if (null == $module) {
            throw new ModuleDoesntExist();
        }
    }

    private function cancelIfEnterpriseDoesntOwnTheModule(
        Enterprise $enterprise,
        Module $module
    ) {
        if (!$enterprise->getModules()->contains($module)) {
            throw new EnterpriseDoesntHaveTheModule();
        }
    }

    private function removeMdouleFromEnterprise(
        Module $module,
        Enterprise $enterprise
    ) {
        $enterprise->removeModule($module);

        $this->enterpriseRepository->save($enterprise);
    }
}
