<?php

namespace Tests\Unit\Traits;

use Addworking\Security\Domain\Models\User;
use Addworking\Security\Domain\Models\Member;
use Addworking\Security\Domain\Models\Module;
use Addworking\Security\Domain\Models\Enterprise;

trait PopulateRepositories
{
    private function populateModules()
    {
        $missionOffer = new Module(
            'jkl',
            'Offre de Mission',
            "007, voici l'offre"
        );

        $this->moduleRepository->add($missionOffer);
        $this->moduleRepository->add(
            new Module('abc', 'Sécurité', "La sécurité c'est iportant :)")
        );
        $this->moduleRepository->add(
            new Module('def', 'Facturation', "Payer, c'est bien :D")
        );
        $this->moduleRepository->add(
            (new Module(
                'ghi',
                'Mission',
                "007, voici votre mission !!"
            ))->addChild($missionOffer)
        );
    }

    private function populateUsers()
    {
        $this->userRepository->add(new User("abc", "Matthieu", "Fravallo"));
        $this->userRepository->add(new User("def", "Jean", "Dupont"));
    }

    private function populateEnterprises()
    {
        $this->enterpriseRepository->add(
            (new Enterprise('abc', 'Addworking'))->addModule(
                $this->moduleRepository->find('abc')
            )
        );
        $this->enterpriseRepository->add(
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

        $this->memberRepository->add(
            (new Member('abc'))
                ->setUser(
                    $this->userRepository->findByName('Matthieu Fravallo')
                )
                ->setEnterprise(
                    $this->enterpriseRepository->findByName('Addworking')
                )
        );

        $this->memberRepository->add(
            (new Member('def'))
                ->setUser($this->userRepository->findByName('Jean Dupont'))
                ->setEnterprise(
                    $this->enterpriseRepository->findByName('Entreprise n°1')
                )
        );
    }
}
