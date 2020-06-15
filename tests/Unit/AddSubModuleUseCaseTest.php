<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Application\Commands\AddSubModule;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Exceptions\ModuleAlreadyExist;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Application\CommandHandlers\AddSubModuleHandler;

class AddSubModuleUseCaseTest extends TestCase
{
    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldAddModuleWhenUserIsSupport()
    {
        $this->authenticateUser('Matthieu Fravallo');

        $parent = $this->moduleRepository->findByName("Mission");

        $this->addSubModule($parent->getId(), "Suivi de Mission", "turlututu");

        $this->assertNotEmpty(
            array_filter(
                $parent->getChildrens(),
                fn(Module $m) => $m->getName() === "Suivi de Mission"
            ),
            "The module {$parent->getName()} should contain Suivi de Mission"
        );
    }

    /** @test */
    public function shouldNotAddSubModuleWhenUserNotSupport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser('Jean Dupont');

        $parent = $this->moduleRepository->findByName("Mission");

        $this->addSubModule($parent->getId(), "Suivi de Mission", "turlututu");
    }

    /** @test */
    public function shouldNotAddSubModuleWhenParentModuleDoesNotExist()
    {
        $this->expectException(ModuleDoesntExist::class);

        $this->authenticateUser('Matthieu Fravallo');

        $this->addSubModule("badId", "Suivi de Mission", "turlututu");
    }

    /** @test */
    public function shouldNotAddSubModuleWhithNameAlreadyTaken()
    {
        $this->expectException(ModuleAlreadyExist::class);

        $this->authenticateUser('Matthieu Fravallo');

        $parent = $this->moduleRepository->findByName("Mission");

        $this->addSubModule($parent->getId(), "Offre de Mission", "turlututu");
    }

    private function addSubModule(string $id, string $name, string $description)
    {
        (new AddSubModuleHandler(
            $this->moduleRepository,
            $this->authenticationGateway,
            $this->authorizationChecker
        ))->handle(new AddSubModule($id, $name, $description));
    }
}
