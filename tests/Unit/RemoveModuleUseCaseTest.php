<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Application\Commands\RemoveModule;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\Services\AuthorizationChecker;
use Addworking\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Addworking\Security\Infrastructure\InMemory\InMemoryUserRepository;
use Addworking\Security\Application\CommandHandlers\RemoveModuleHandler;
use Addworking\Security\Infrastructure\InMemory\InMemoryMemberRepository;
use Addworking\Security\Infrastructure\InMemory\InMemoryModuleRepository;
use Addworking\Security\Infrastructure\InMemory\InMemoryEnterpriseRepository;
use Addworking\Security\Infrastructure\InMemory\InMemoryAuthenticationGateway;

class RemoveModuleUseCaseTest extends TestCase
{
    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    protected function setUp(): void
    {
        $this->memberRepository = new InMemoryMemberRepository();
        $this->userRepository = new InMemoryUserRepository();
        $this->enterpriseRepository = new InMemoryEnterpriseRepository();
        $this->moduleRepository = new InMemoryModuleRepository();
        $this->authenticationGateway = new InMemoryAuthenticationGateway();
        $this->authorizationChecker = new AuthorizationChecker(
            $this->memberRepository, $this->enterpriseRepository
        );

        $this->populateMembers();
    }

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
