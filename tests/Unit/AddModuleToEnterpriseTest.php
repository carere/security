<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Application\CommandHandlers\AddModuleToEnterpriseHandler;
use Ashiso\Security\Application\Commands\AddModuleToEnterprise;
use Ashiso\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;

class AddModuleToEnterpriseTest extends TestCase
{
    const ENTERPRISE_1 = "Entreprise n°1";
    const MODULE_MISSION = "Mission";
    const MODULE_FACTURATION = "Facturation";

    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldAddModuleToEnterpriseWhenUserIsSupport()
    {
        $this->authenticateUser("Matthieu Fravallo");

        $this->addModuleToEnterprise(
            $this->moduleRepository->findByName(self::MODULE_MISSION)->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );

        $this->assertThatEnterpriseHasAccessToModule(
            self::ENTERPRISE_1,
            self::MODULE_MISSION
        );
    }

    /** @test */
    public function shouldNotBeAbleToAddModuleToUnexistantEnterprise()
    {
        $this->expectException(EnterpriseDoesntExist::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->addModuleToEnterprise(
            $this->moduleRepository->findByName(self::MODULE_MISSION)->getId(),
            "Turlututu"
        );
    }

    /** @test */
    public function shouldNotBeAbleToAddUnexistantModuleToEnterprise()
    {
        $this->expectException(ModuleDoesntExist::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->addModuleToEnterprise(
            "Turlututu",
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    /** @test */
    public function shouldNotBeAbleToAddModuleToEnterpriseWhenUserIsNotSupport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser("Jean Dupont");

        $this->addModuleToEnterprise(
            $this->moduleRepository->findByName(self::MODULE_MISSION)->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    /** @test */
    public function shouldNotBeAbleToAddAnAlreadyOwnModuleToEnterprise()
    {
        $this->expectException(EnterpriseAlreadyHaveModule::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->addModuleToEnterprise(
            $this->moduleRepository
                ->findByName(self::MODULE_FACTURATION)
                ->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    private function addModuleToEnterprise(
        string $moduleId,
        string $enterpriseId
    ) {
        (new AddModuleToEnterpriseHandler(
            $this->enterpriseRepository,
            $this->moduleRepository,
            $this->authenticationGateway,
            $this->authorizationChecker
        ))->handle(new AddModuleToEnterprise($moduleId, $enterpriseId));
    }

    private function assertThatEnterpriseHasAccessToModule(
        string $enterpriseName,
        string $moduleName
    ) {
        $this->assertTrue(
            $this->enterpriseRepository
                ->findByName($enterpriseName)
                ->getModules()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() === $moduleName
                ),
            "The enterprise '{$enterpriseName}' should have access to module '{$moduleName}'"
        );
    }
}
