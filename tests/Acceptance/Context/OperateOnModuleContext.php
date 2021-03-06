<?php

namespace Tests\Acceptance\Context;

use Tests\Application;
use PHPUnit\Framework\TestCase;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Models\Member;
use Ashiso\Security\Domain\Models\Module;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Ashiso\Security\Domain\Models\Enterprise;
use Ashiso\Security\Application\Commands\EditModule;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Exceptions\MemberNotAdmin;
use Ashiso\Security\Application\Commands\AddSubModule;
use Ashiso\Security\Application\Commands\CreateModule;
use Ashiso\Security\Application\Commands\RemoveModule;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Exceptions\ModuleDoesntExist;
use Ashiso\Security\Domain\Exceptions\ModuleAlreadyExist;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntExist;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Application\Commands\AddModuleToEnterprise;
use Ashiso\Security\Application\CommandHandlers\EditModuleHandler;
use Ashiso\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Ashiso\Security\Application\CommandHandlers\AddSubModuleHandler;
use Ashiso\Security\Application\CommandHandlers\CreateModuleHandler;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleHandler;
use Ashiso\Security\Application\Commands\RemoveModuleFromEnterprise;
use Ashiso\Security\Domain\Exceptions\EnterpriseDoesntHaveTheModule;
use Ashiso\Security\Application\CommandHandlers\AddModuleToEnterpriseHandler;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleFromEnterpriseHandler;

class OperateOnModuleContext extends TestCase implements Context
{
    private UserRepository $userRepository;
    private EnterpriseRepository $enterpriseRepository;
    private MemberRepository $memberRepository;
    private ModuleRepository $moduleRepository;
    private AuthenticationGateway $authenticationGateway;
    private AuthorizationChecker $authorizationChecker;
    private array $errorsThrown = [];

    public function __construct()
    {
        $container = Application::getContainer();

        $this->userRepository = $container->get(UserRepository::class);
        $this->enterpriseRepository = $container->get(
            EnterpriseRepository::class
        );
        $this->memberRepository = $container->get(MemberRepository::class);
        $this->moduleRepository = $container->get(ModuleRepository::class);
        $this->authenticationGateway = $container->get(
            AuthenticationGateway::class
        );
        $this->authorizationChecker = $container->get(
            AuthorizationChecker::class
        );
    }

    /** @BeforeSuite */
    public static function prepare(BeforeSuiteScope $scope)
    {
        Application::initEnv();
    }

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        Application::resetDatabase();
    }

    /**
     * @Given /^des utilisateurs existent$/
     */
    public function desUtilisateursExistent(TableNode $users)
    {
        foreach ($users as $user) {
            $this->userRepository->save(
                new User($user['id'], $user['firstname'], $user['lastname'])
            );
        }
    }

    /**
     * @Given /^des modules existent$/
     */
    public function desModulesExistent(PyStringNode $modulesAsJson)
    {
        $modules = json_decode($modulesAsJson, true);

        foreach ($modules as $module) {
            $this->registerModuleAndChild($module);
        }
    }

    private function registerModuleAndChild(array $module): Module
    {
        $parent = new Module(
            $module['id'],
            $module['name'],
            $module['description']
        );

        if (isset($module['modules'])) {
            array_map(
                fn(array $child) => $parent->addChild(
                    $this->registerModuleAndChild($child)->setParent($parent)
                ),
                $module['modules']
            );
        }

        $this->moduleRepository->save($parent);

        return $parent;
    }

    /**
     * @Given /^des entreprises existent$/
     */
    public function desEntreprisesExistent(TableNode $enterprises)
    {
        foreach ($enterprises as $enterprise) {
            $e = new Enterprise($enterprise['id'], $enterprise['name']);

            array_map(
                fn(string $module) => $e->addModule(
                    $this->moduleRepository->findByName($module)
                ),
                explode(',', $enterprise['modules'])
            );

            $this->enterpriseRepository->save($e);
        }
    }

    /**
     * @Given /^des membres d\'entreprises existent$/
     */
    public function desMembresDentreprisesExistent(TableNode $members)
    {
        foreach ($members as $member) {
            $user = $this->userRepository->findByName($member['user']);
            $enterprise = $this->enterpriseRepository->findByName(
                $member['enterprise']
            );

            $this->memberRepository->save(
                (new Member($member['id']))
                    ->setUser($user)
                    ->setEnterprise($enterprise)
            );
        }
    }

    /**
     * @Given /^je suis authentifié en tant que "([^"]*)"$/
     */
    public function jeSuisAuthentifieEnTantQue(string $userName)
    {
        $user = $this->userRepository->findByName($userName);

        $this->authenticationGateway->authenticate($user);
    }

    /**
     * @When /^j\'essaye de créer un module nommé "([^"]*)" ayant pour description "([^"]*)"$/
     */
    public function jessayeDeCreerUnModuleNomme(
        string $moduleName,
        string $description
    ) {
        try {
            (new CreateModuleHandler(
                $this->moduleRepository,
                $this->authenticationGateway,
                $this->authorizationChecker
            ))->handle(new CreateModule($moduleName, $description));
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^le module "([^"]*)" est créé avec pour description "([^"]*)"$/
     */
    public function leModuleEstCree(string $moduleName, string $description)
    {
        $module = $this->moduleRepository->findByName($moduleName);

        $this->assertNotNull(
            $module,
            "The module {$moduleName} should exist !!"
        );
        $this->assertEquals(
            $description,
            $module->getDescription(),
            "Description should be the same !!"
        );
    }

    /**
     * @Then /^une erreur est levée indiquant que un module du même nom existe déjà$/
     */
    public function uneErreurEstLeveeIndiquantQueUnModuleDuMemeNomExisteDeja()
    {
        $this->assertContainsEquals(
            ModuleAlreadyExist::class,
            $this->errorsThrown
        );
    }

    /**
     * @Then /^une erreur est levée indiquant que le membre n\'est pas support$/
     */
    public function uneErreurEstLeveeIndiquantQueLeMembreNestPasSupport()
    {
        $this->assertContainsEquals(MemberNotAdmin::class, $this->errorsThrown);
    }

    /**
     * @When /^j\'essaye de supprimer le module nommé "([^"]*)"$/
     */
    public function jessayeDeSupprimerLeModuleNomme(string $moduleName)
    {
        try {
            $module = $this->moduleRepository->findByName($moduleName);

            if (null === $module) {
                throw new ModuleDoesntExist();
            }

            (new RemoveModuleHandler(
                $this->moduleRepository,
                $this->enterpriseRepository,
                $this->authorizationChecker,
                $this->authenticationGateway
            ))->handle(new RemoveModule($module->getId()));
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^le module "([^"]*)" est supprimé$/
     */
    public function leModuleEstSupprime(string $moduleName)
    {
        $this->assertNull($this->moduleRepository->findByName($moduleName));
    }

    /**
     * @Then /^une erreur est levée indiquant que le module n\'existe pas$/
     */
    public function uneErreurEstLeveeIndiquantQueLeModuleNexistePas()
    {
        $this->assertContainsEquals(
            ModuleDoesntExist::class,
            $this->errorsThrown
        );
    }

    /**
     * @Then /^une erreur est levée indiquant que le module est déjà possédé par une entreprise$/
     */
    public function uneErreurEstLeveeIndiquantQueLeModuleEstDejaPossedeParUneEntreprise()
    {
        $this->assertContainsEquals(
            EnterpriseAlreadyHaveModule::class,
            $this->errorsThrown
        );
    }

    /**
     * @When /^j\'essaye de modifier la description du module nommé "([^"]*)" par "([^"]*)"$/
     */
    public function jessayeDeModifierLaDescriptionDuModuleNommePar(
        string $moduleName,
        string $description
    ) {
        try {
            $module = $this->moduleRepository->findByName($moduleName);

            if (null === $module) {
                throw new ModuleDoesntExist();
            }

            (new EditModuleHandler(
                $this->moduleRepository,
                $this->authenticationGateway,
                $this->authorizationChecker
            ))->handle(new EditModule($module->getId(), $description));
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^le module "([^"]*)" devrait avoir pour description "([^"]*)"$/
     */
    public function leModuleDevraitAvoirPourDescription(
        string $moduleName,
        string $description
    ) {
        $this->assertEquals(
            $description,
            $this->moduleRepository->findByName($moduleName)->getDescription()
        );
    }

    /**
     * @When /^j\'essaye d\'ajouter un sous-modules nommé "([^"]*)" ayant pour description "([^"]*)" au module "([^"]*)"$/
     */
    public function jessayeDajouterUnSousModulesNommeAyantPourDescriptionAuModule(
        string $childName,
        string $childDescription,
        string $parentName
    ) {
        try {
            $module = $this->moduleRepository->findByName($parentName);

            if (null === $module) {
                throw new ModuleDoesntExist();
            }

            (new AddSubModuleHandler(
                $this->moduleRepository,
                $this->authenticationGateway,
                $this->authorizationChecker
            ))->handle(
                new AddSubModule(
                    $module->getId(),
                    $childName,
                    $childDescription
                )
            );
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^le module "([^"]*)" devrait posséder un module nommé "([^"]*)"$/
     */
    public function leModuleDevraitPossederUnModuleNomme(
        string $parentName,
        string $childName
    ) {
        $parent = $this->moduleRepository->findByName($parentName);

        $this->assertTrue(
            $parent
                ->getChildrens()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() === $childName
                ),
            "The module {$parentName} should contain {$childName}"
        );
    }

    /**
     * @When /^j\'essaye d\'ajouter le module "([^"]*)" à l\'entreprise "([^"]*)"$/
     */
    public function jessayeDajouterLeModuleALentreprise(
        string $moduleName,
        string $enterpriseName
    ) {
        try {
            $enterprise = $this->enterpriseRepository->findByName(
                $enterpriseName
            );

            if (null === $enterprise) {
                throw new EnterpriseDoesntExist();
            }

            $module = $this->moduleRepository->findByName($moduleName);

            if (null === $module) {
                throw new ModuleDoesntExist();
            }

            (new AddModuleToEnterpriseHandler(
                $this->enterpriseRepository,
                $this->moduleRepository,
                $this->authenticationGateway,
                $this->authorizationChecker
            ))->handle(
                new AddModuleToEnterprise(
                    $module->getId(),
                    $enterprise->getId()
                )
            );
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^l\'entreprise "([^"]*)" possède le module "([^"]*)"$/
     */
    public function lentreprisePossedeLeModule(
        string $enterpriseName,
        string $moduleName
    ) {
        $enterprise = $this->enterpriseRepository->findByName($enterpriseName);

        $this->assertTrue(
            $enterprise
                ->getModules()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() === $moduleName
                ),
            "The enterprise '{$enterpriseName}' should have access to module '{$moduleName}'"
        );
    }

    /**
     * @Then /^une erreur est levée indiquant que l\'entreprise n\'existe pas$/
     */
    public function uneErreurEstLeveeInfiquantQueLenrepriseNexistePas()
    {
        $this->assertContainsEquals(
            EnterpriseDoesntExist::class,
            $this->errorsThrown
        );
    }

    /**
     * @When /^j\'essaye de retirer le module "([^"]*)" de l\'entreprise "([^"]*)"$/
     */
    public function jessayeDeRetirerLeModuleDeLentreprise(
        string $moduleName,
        string $enterpriseName
    ) {
        try {
            $enterprise = $this->enterpriseRepository->findByName(
                $enterpriseName
            );

            if (null === $enterprise) {
                throw new EnterpriseDoesntExist();
            }

            $module = $this->moduleRepository->findByName($moduleName);

            if (null === $module) {
                throw new ModuleDoesntExist();
            }

            (new RemoveModuleFromEnterpriseHandler(
                $this->moduleRepository,
                $this->enterpriseRepository,
                $this->authenticationGateway,
                $this->authorizationChecker
            ))->handle(
                new RemoveModuleFromEnterprise(
                    $module->getId(),
                    $enterprise->getId()
                )
            );
        } catch (\Exception $e) {
            $this->errorsThrown[] = get_class($e);
        }
    }

    /**
     * @Then /^l\'entreprise "([^"]*)" ne possède plus le module "([^"]*)"$/
     */
    public function lentrepriseNePossedePlusLeModule(
        string $enterpriseName,
        string $moduleName
    ) {
        $enterprise = $this->enterpriseRepository->findByName($enterpriseName);

        $this->assertFalse(
            $enterprise
                ->getModules()
                ->exists(
                    fn(int $key, Module $m) => $m->getName() === $moduleName
                ),
            "The enterprise '{$enterpriseName}' should not have acces to module '{$moduleName}'"
        );
    }

    /**
     * @Then /^une erreur est levée indiquant que l\'entreprise ne possède pas le module$/
     */
    public function uneErreurEstLeveeIndiquantQueLentrepriseNePossedePasLeModule()
    {
        $this->assertContainsEquals(
            EnterpriseDoesntHaveTheModule::class,
            $this->errorsThrown
        );
    }
}
