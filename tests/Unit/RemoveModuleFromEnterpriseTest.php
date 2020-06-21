<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\Commands\RemoveModuleFromEnterprise;
use Addworking\Security\Domain\Exceptions\EnterpriseDoesntHaveTheModule;
use Addworking\Security\Application\CommandHandlers\RemoveModuleFromEnterpriseHandler;

class RemoveModuleFromEnterpriseTest extends TestCase
{
    const MODULE_FACTURATION = "Facturation";
    const MODULE_MISSION = "Mission";
    const MODULE_SECURITY = "Sécurité";
    const ENTERPRISE_1 = "Entreprise n°1";

    use PopulateRepositories, Authentication;

    private MemberRepository $memberRepository;
    private UserRepository $userRepository;
    private ModuleRepository $moduleRepository;
    private EnterpriseRepository $enterpriseRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;

    /** @test */
    public function shouldRemoveModuleFromEnterpriseWhenUserIsSupport()
    {
        $this->authenticateUser("Matthieu Fravallo");

        $this->removeModuleFromEnterprise(
            $this->moduleRepository
                ->findByName(self::MODULE_FACTURATION)
                ->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );

        $this->assertThatEnterpriseDoesntHaveAccessToModule(
            self::ENTERPRISE_1,
            self::MODULE_FACTURATION
        );
    }

    /** @test */
    public function shouldNotBeAbleToRemoveModuleFromUnexistantEnterprise()
    {
        $this->expectException(EnterpriseDoesntExist::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->removeModuleFromEnterprise(
            $this->moduleRepository->findByName(self::MODULE_MISSION)->getId(),
            "Turlututu"
        );
    }

    /** @test */
    public function shouldNotBeAbleToRemoveUnexistantModuleFromEnterprise()
    {
        $this->expectException(ModuleDoesntExist::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->removeModuleFromEnterprise(
            "Turlututu",
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    /** @test */
    public function shouldNotBeAbleToRemoveModuleFromEnterpriseWhenUserIsNotSupport()
    {
        $this->expectException(MemberNotAdmin::class);

        $this->authenticateUser("Jean Dupont");

        $this->removeModuleFromEnterprise(
            $this->moduleRepository
                ->findByName(self::MODULE_FACTURATION)
                ->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    /** @test */
    public function shouldNotBeAbleToRemoveModuleNotAlreadyOwnFromEnterprise()
    {
        $this->expectException(EnterpriseDoesntHaveTheModule::class);

        $this->authenticateUser("Matthieu Fravallo");

        $this->removeModuleFromEnterprise(
            $this->moduleRepository->findByName(self::MODULE_SECURITY)->getId(),
            $this->enterpriseRepository->findByName(self::ENTERPRISE_1)->getId()
        );
    }

    private function removeModuleFromEnterprise(
        string $moduleId,
        string $enterpriseId
    ) {
        (new RemoveModuleFromEnterpriseHandler(
            $this->moduleRepository,
            $this->enterpriseRepository,
            $this->authenticationGateway,
            $this->authorizationChecker
        ))->handle(new RemoveModuleFromEnterprise($moduleId, $enterpriseId));
    }

    private function assertThatEnterpriseDoesntHaveAccessToModule(
        string $enterpriseName,
        string $moduleName
    ) {
        $this->assertFalse(
            $this->enterpriseRepository
                ->findByName($enterpriseName)
                ->getModules()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() === $moduleName
                ),
            "The enterprise '{$enterpriseName}' should not have access to module '{$moduleName}'"
        );
    }
}
