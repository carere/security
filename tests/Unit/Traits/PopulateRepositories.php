<?php

namespace Tests\Unit\Traits;

use Tests\Application;
use Ashiso\Security\Domain\Models\User;
use Ashiso\Security\Domain\Models\Member;
use Ashiso\Security\Domain\Models\Module;
use Ashiso\Security\Domain\Models\Enterprise;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Repositories\ModuleRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;

trait PopulateRepositories
{
    /**
     * @beforeClass
     */
    public static function initializeEnvironment()
    {
        Application::initEnv();
    }

    /**
     * @before
     */
    public function resetDatabase()
    {
        Application::resetDatabase();
    }

    protected function setUp(): void
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

        $this->populateMembers();
    }

    private function populateModules()
    {
        $missionOffer = new Module(
            'jkl',
            'Offre de Mission',
            "007, voici l'offre"
        );

        $this->moduleRepository->save($missionOffer);
        $this->moduleRepository->save(
            new Module('abc', 'Sécurité', "La sécurité c'est iportant :)")
        );
        $this->moduleRepository->save(
            new Module('def', 'Facturation', "Payer, c'est bien :D")
        );
        $this->moduleRepository->save(
            (new Module(
                'ghi',
                'Mission',
                "007, voici votre mission !!"
            ))->addChild($missionOffer)
        );
    }

    private function populateUsers()
    {
        $this->userRepository->save(new User("abc", "Matthieu", "Fravallo"));
        $this->userRepository->save(new User("def", "Jean", "Dupont"));
    }

    private function populateEnterprises()
    {
        $this->enterpriseRepository->save(
            (new Enterprise(
                'f1494810-ed7a-406f-8aeb-7845c4105b01',
                'Ashiso'
            ))->addModule($this->moduleRepository->find('abc'))
        );
        $this->enterpriseRepository->save(
            (new Enterprise('def', 'Entreprise n°1'))->addModule(
                $this->moduleRepository->find('def')
            )
        );
    }

    private function populateMembers()
    {
        $this->populateModules();
        $this->populateUsers();
        $this->populateEnterprises();

        $this->memberRepository->save(
            (new Member('abc'))
                ->setUser(
                    $this->userRepository->findByName('Matthieu Fravallo')
                )
                ->setEnterprise(
                    $this->enterpriseRepository->findByName('Ashiso')
                )
        );

        $this->memberRepository->save(
            (new Member('def'))
                ->setUser($this->userRepository->findByName('Jean Dupont'))
                ->setEnterprise(
                    $this->enterpriseRepository->findByName('Entreprise n°1')
                )
        );
    }
}
