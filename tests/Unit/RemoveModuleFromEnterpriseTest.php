<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Traits\Authentication;
use Tests\Unit\Traits\PopulateRepositories;
use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\Commands\RemoveModuleFromEnterprise;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntHaveTheModule;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleFromEnterpriseHandler;

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
