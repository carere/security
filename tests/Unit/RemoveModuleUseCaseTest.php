<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Application\Commands\RemoveModule;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleHandler;

class RemoveModuleUseCaseTest extends TestCase
{
    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldRemoveModuleWhenUserIsSupport()
    {
        $this->authenticateUser('Matthieu Fravallo');

        $moduleMission = $this->moduleRepository->findByName("Mission");

        $this->removeModule($moduleMission->getId());

        $this->assertNull($this->moduleRepository->findByName("Mission"));
    }

    /** @test */
    public function shouldNotRemoveModuleWhenModuleDoesntExist()
    {
        $this->expectException(ModuleDoesntExist::class);

        $this->authenticateUser('Matthieu Fravallo');

        $this->removeModule("badId");
    }

    /** @test */
    public function shouldNotRemoveModuleWhenUserNotSuuport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser('Jean Dupont');

        $moduleMission = $this->moduleRepository->findByName("Mission");

        $this->removeModule($moduleMission->getId());
    }

    /** @test */
    public function shouldNotRemoveModuleIfAnyEnterpriseHasAccessToIt()
    {
        $this->expectException(EnterpriseAlreadyHaveModule::class);

        $this->authenticateUser('Matthieu Fravallo');

        $moduleMission = $this->moduleRepository->findByName("Facturation");

        $this->removeModule($moduleMission->getId());
    }

    private function removeModule(string $id)
    {
        (new RemoveModuleHandler(
            $this->moduleRepository,
            $this->enterpriseRepository,
            $this->authorizationChecker,
            $this->authenticationGateway
        ))->handle(new RemoveModule($id));
    }
}
