<?php

namespace Addworking\Security\Application\CommandHandlers;

use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Application\Commands\AddSubModule;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Domain\Exceptions\ModuleAlreadyExist;

class AddSubModuleHandler
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

    public function handle(AddSubModule $command)
    {
        $this->cancelIfUserNotAdmin();

        $parent = $this->moduleRepository->find($command->getId());

        $this->cancelIfModuleDoesntExist($parent);

        $this->cancelIfChildNameAlreadyTaken($command->getName());

        $child = new Module(
            $this->moduleRepository->nextIdentity(),
            $command->getName(),
            $command->getDescription()
        );

        $this->addSubModule($parent, $child);
    }

    private function cancelIfUserNotAdmin()
    {
        $authenticatedUser = $this->authenticationGateway->connectedUser();

        if (!$this->authorizationChecker->isSupport($authenticatedUser)) {
            throw new MemberNotAdmin();
        }
    }

    private function cancelIfModuleDoesntExist(?Module $module)
    {
        if (null === $module) {
            throw new ModuleDoesntExist();
        }
    }

    private function cancelIfChildNameAlreadyTaken(string $name)
    {
        if (null !== $this->moduleRepository->findByName($name)) {
            throw new ModuleAlreadyExist();
        }
    }

    private function addSubModule(Module $parent, Module $child)
    {
        $parent->addChild($child);

        $this->moduleRepository->save($parent);
    }
}
