<?php

namespace Ashiso\Security\Application\CommandHandlers;

use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Application\Commands\EditModule;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Application\AuthorizationChecker;

class EditModuleHandler
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

    public function handle(EditModule $command)
    {
        $this->cancelIfUserNotAdmin();

        $moduleToEdit = $this->moduleRepository->find($command->getId());

        $this->cancelIfModuleDoesntExist($moduleToEdit);

        $this->editDescription($moduleToEdit, $command->getDescription());
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

    private function editDescription(Module $module, string $description)
    {
        $module->setDescription($description);

        $this->moduleRepository->save($module);
    }
}
