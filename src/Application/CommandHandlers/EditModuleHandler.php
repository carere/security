<?php

namespace Addworking\Security\Application\CommandHandlers;

use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Application\Commands\EditModule;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Application\Services\AuthorizationChecker;

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
