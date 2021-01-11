<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Application\Commands\AddSubModule;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Exceptions\ModuleAlreadyExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\CommandHandlers\AddSubModuleHandler;

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

        $this->assertTrue(
            $parent
                ->getChildrens()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() ===
                        "Suivi de Mission"
                ),
            "The module {$parent->getName()} should contain 'Suivi de Mission'"
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
