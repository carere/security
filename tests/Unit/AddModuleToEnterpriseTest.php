<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Application\CommandHandlers\AddModuleToEnterpriseHandler;
use Addworking\Security\Application\Commands\AddModuleToEnterprise;
use Addworking\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Addworking\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;

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
