<?php

namespace Ashiso\Security\Application\CommandHandlers;

use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Application\Commands\CreateModule;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\ModuleAlreadyExist;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;

class CreateModuleHandler
{
    private ModuleRepository $moduleRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        ModuleRepository $moduleRepository,
        AuthenticationGateway $authenticationGateway,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->moduleRepository = $moduleRepository;
        $this->authenticationGateway = $authenticationGateway;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function handle(CreateModule $command): void
    {
        $this->cancelIfUserNotAdmin();

        $this->cancelIfModuleNameAlreadyTaken($command->getName());

        $this->createModule($command->getName(), $command->getDescription());
    }

    private function cancelIfUserNotAdmin()
    {
        $authenticatedUser = $this->authenticationGateway->connectedUser();

        if (!$this->authorizationChecker->isSupport($authenticatedUser)) {
            throw new MemberNotAdmin();
        }
    }

    private function cancelIfModuleNameAlreadyTaken(string $name)
    {
        if (null !== $this->moduleRepository->findByName($name)) {
            throw new ModuleAlreadyExist();
        }
    }

    private function createModule(string $name, string $description)
    {
        $module = new Module(
            $this->moduleRepository->nextIdentity(),
            $name,
            $description
        );

        $this->moduleRepository->save($module);
    }
}
