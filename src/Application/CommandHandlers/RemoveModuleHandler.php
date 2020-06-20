<?php

namespace Addworking\Security\Application\CommandHandlers;

use Addworking\Security\Application\Commands\RemoveModule;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;

class RemoveModuleHandler
{
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthorizationChecker $authorizationChecker;
    private AuthenticationGateway $authenticationGateway;

    public function __construct(
        ModuleRepository $moduleRepository,
        EnterpriseRepository $enterpriseRepository,
        AuthorizationChecker $authorizationChecker,
        AuthenticationGateway $authenticationGateway
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->enterpriseRepository = $enterpriseRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->authenticationGateway = $authenticationGateway;
    }

    public function handle(RemoveModule $command)
    {
        $this->cancelIfUserNotAdmin();

        $this->cancelIfModuleStillLinkToAnEnterprise($command->getModuleId());

        $module = $this->moduleRepository->find($command->getModuleId());

        if (null === $module) {
            throw new ModuleDoesntExist();
        }

        $this->moduleRepository->delete($module);
    }

    private function cancelIfUserNotAdmin()
    {
        $authenticatedUser = $this->authenticationGateway->connectedUser();

        if (!$this->authorizationChecker->isSupport($authenticatedUser)) {
            throw new MemberNotAdmin();
        }
    }

    private function cancelIfModuleStillLinkToAnEnterprise(string $moduleId)
    {
        if (!empty($this->enterpriseRepository->findByModuleId($moduleId))) {
            throw new EnterpriseAlreadyHaveModule();
        }
    }
}
