<?php

namespace Tests\Acceptance\Context;

use Tests\Application;
use PHPUnit\Framework\TestCase;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Models\Member;
use Addworking\Security\Domain\Models\Module;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Addworking\Security\Domain\Models\Enterprise;
use Addworking\Security\Application\Commands\EditModule;
use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Domain\Exceptions\MemberNotAdmin;
use Addworking\Security\Application\Commands\AddSubModule;
use Addworking\Security\Application\Commands\CreateModule;
use Addworking\Security\Application\Commands\RemoveModule;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Domain\Exceptions\ModuleDoesntExist;
use Addworking\Security\Domain\Exceptions\ModuleAlreadyExist;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Addworking\Security\Domain\Repositories\ModuleRepository;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Application\CommandHandlers\EditModuleHandler;
use Addworking\Security\Domain\Exceptions\EnterpriseAlreadyHaveModule;
use Addworking\Security\Application\CommandHandlers\AddSubModuleHandler;
use Addworking\Security\Application\CommandHandlers\CreateModuleHandler;
use Addworking\Security\Application\CommandHandlers\RemoveModuleHandler;

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
            $this->userRepository->add(
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

        $this->moduleRepository->add($parent);

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

            $this->enterpriseRepository->add($e);
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

            $this->memberRepository->add(
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
        $module = $this->moduleRepository->findByName($parentName);

        $this->assertNotEmpty(
            array_filter(
                $module->getChildrens(),
                fn(Module $m) => $m->getName() === $childName
            ),
            "The module {$parentName} should contain {$childName}"
        );
    }
}
