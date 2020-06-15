<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Addworking\Security\Application\Commands\EditModule;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Application\CommandHandlers\EditModuleHandler;

class EditModuleUseCaseTest extends TestCase
{
    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldEditModuleWhenUserIsSupport()
    {
        $this->authenticateUser('Matthieu Fravallo');

        $moduleId = $this->moduleRepository->findByName("Facturation")->getId();

        $this->editModule($moduleId, "Turlututu");

        $this->assertEquals(
            "Turlututu",
            $this->moduleRepository->find($moduleId)->getDescription()
        );
    }

    /** @test */
    public function shouldNotEditUnexistingModule()
    {
        $this->expectException(ModuleDoesntExist::class);

        $this->authenticateUser('Matthieu Fravallo');

        $this->editModule("badId", "Où est la description ??");
    }

    /** @test */
    public function shouldNotEditModuleWhenNonSupport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser('Jean Dupont');

        $moduleId = $this->moduleRepository->findByName("Mission")->getId();

        $this->editModule($moduleId, "Où est la description ??");
    }

    private function editModule(string $moduleId, string $description)
    {
        (new EditModuleHandler(
            $this->moduleRepository,
            $this->authenticationGateway,
            $this->authorizationChecker
        ))->handle(new EditModule($moduleId, $description));
    }
}
