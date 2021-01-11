<?php

namespace Ashiso\Security\Application\CommandHandlers;

use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Domain\Models\Enterprise;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\Commands\AddModuleToEnterprise;
use Ashiso\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;

class AddModuleToEnterpriseHandler
{
    private EnterpriseRepository $enterpriseRepository;
    private ModuleRepository $moduleRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        EnterpriseRepository $enterpriseRepository,
        ModuleRepository $moduleRepository,
        AuthenticationGateway $authenticationGateway,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->enterpriseRepository = $enterpriseRepository;
        $this->moduleRepository = $moduleRepository;
        $this->authenticationGateway = $authenticationGateway;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function handle(AddModuleToEnterprise $command)
    {
        $this->cancelIfUserNotAdmin();

        $enterprise = $this->enterpriseRepository->find(
            $command->getEnterpriseId()
        );

        $this->cancelIfEnterpriseDoesNotExist($enterprise);

        $module = $this->moduleRepository->find($command->getModuleId());

        $this->cancelIfModuleDoesNotExist($module);

        $this->cancelIfEnterpriseAlreadyOwnTheModule($enterprise, $module);

        $this->addModuleToEnterprise($module, $enterprise);
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

    private function cancelIfEnterpriseAlreadyOwnTheModule(
        Enterprise $enterprise,
        Module $module
    ) {
        if (
            $enterprise
                ->getModules()
                ->exists(
                    fn(int $key, Module $m) => $m->getId() === $module->getId()
                )
        ) {
            throw new EnterpriseAlreadyHaveModule();
        }
    }

    private function addModuleToEnterprise(
        Module $module,
        Enterprise $enterprise
    ) {
        $enterprise->addModule($module);

        $this->enterpriseRepository->save($enterprise);
    }
}
