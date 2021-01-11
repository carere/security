<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Application\Commands\CreateModule;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleAlreadyExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\CommandHandlers\CreateModuleHandler;

class CreateModuleUseCaseTest extends TestCase
{
    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldCreateModuleWhenUserIsSupport()
    {
        $this->authenticateUser('Matthieu Fravallo');

        $this->createModule("Contrats", "Les contrats, c'est la vie !!");

        $module = $this->moduleRepository->findByName("Contrats");

        $this->assertModuleExist($module);
        $this->assertModuleDescriptionEquals(
            $module,
            "Les contrats, c'est la vie !!"
        );
    }

    /** @test */
    public function shouldNotCreateModuleWhenNameProvidedAlreadyExist()
    {
        $this->expectException(ModuleAlreadyExist::class);

        $this->authenticateUser('Matthieu Fravallo');

        $this->createModule("Sécurité", "Dummy description !!");
    }

    /** @test */
    public function shouldNotCreateModuleWhenUserIsNotSupport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser('Jean Dupont');

        $this->createModule("Contrats", "Dummy description !!");
    }

    private function createModule(string $name, string $description)
    {
        (new CreateModuleHandler(
            $this->moduleRepository,
            $this->authenticationGateway,
            $this->authorizationChecker
        ))->handle(new CreateModule($name, $description));
    }

    private function assertModuleExist(?Module $module)
    {
        $this->assertNotNull(
            $module,
            "The module {$module->getName()} should exist !!"
        );
    }

    private function assertModuleDescriptionEquals(
        Module $module,
        string $description
    ) {
        $this->assertEquals($description, $module->getDescription());
    }
}
