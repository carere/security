<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Application\Commands\EditModule;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Application\CommandHandlers\EditModuleHandler;

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
