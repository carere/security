<?php

namespace Ashiso\Security\Application\CommandHandlers;

use Ashiso\Security\Application\Commands\RemoveModule;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;

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
