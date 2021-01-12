@operate-on-module
Feature: Manipuler les modules

    Background:
        Given des utilisateurs existent
            | id  | firstname | lastname |
            | abc | Matthieu  | Fravallo |
            | def | Jean      | Dupont   |
        And des modules existent
            """
            [
                {
                    "id": "abc",
                    "name": "Sécurité",
                    "description": "La sécurité c'est iportant :)"
                },
                {
                    "id": "def",
                    "name": "Facturation",
                    "description": "Payer, c'est bien :D"
                },
                {
                    "id": "ghi",
                    "name": "Mission",
                    "description": "007, voici votre mission !!",
                    "modules": [
                        {
                            "id": "jkl",
                            "name": "Offre de Mission",
                            "description": "007, voici l'offre"
                        }
                    ]
                }
            ]
            """
        And des entreprises existent
            | id                                   | name           | modules     |
            | f1494810-ed7a-406f-8aeb-7845c4105b01 | Ashiso         | Sécurité    |
            | def                                  | Entreprise n°1 | Facturation |
        And des membres d'entreprises existent
            | id  | user              | enterprise     |
            | abc | Matthieu Fravallo | Ashiso         |
            | def | Jean Dupont       | Entreprise n°1 |

    Scenario: Créer un module
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de créer un module nommé "Contrats" ayant pour description "Les contrats, c'est la vie !!"
        Then le module "Contrats" est créé avec pour description "Les contrats, c'est la vie !!"

    Scenario: Impossibilité de créer un module qui a le même nom
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de créer un module nommé "Sécurité" ayant pour description "Dummy description !!"
        Then une erreur est levée indiquant que un module du même nom existe déjà

    Scenario: Impossibilité de créer un module si non support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye de créer un module nommé "Contrats" ayant pour description "Dummy description !!"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Supprimer un module
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de supprimer le module nommé "Mission"
        Then le module "Mission" est supprimé

    Scenario: Impossibilité de supprimer un module non existant
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de supprimer le module nommé "Tartenpion"
        Then une erreur est levée indiquant que le module n'existe pas

    Scenario: Impossibilité de supprimer un module si non support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye de supprimer le module nommé "Mission"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Impossibilité de supprimer un module si une entreprise possède le module
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de supprimer le module nommé "Facturation"
        Then une erreur est levée indiquant que le module est déjà possédé par une entreprise

    Scenario: Modifier un module
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de modifier la description du module nommé "Facturation" par "Payer turlututu"
        Then le module "Facturation" devrait avoir pour description "Payer turlututu"

    Scenario: Impossibilité de modifier un module non existant
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de modifier la description du module nommé "Tartenpion" par "Où est la description ??"
        Then une erreur est levée indiquant que le module n'existe pas

    Scenario: Impossibilité de modifier un module si non support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye de modifier la description du module nommé "Mission" par "Où est la description ??"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Ajouter un sous-module
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter un sous-modules nommé "Suivi de Mission" ayant pour description "turlututu" au module "Mission"
        Then le module "Mission" devrait posséder un module nommé "Suivi de Mission"

    Scenario: Impossibilité d'ajouter un sous-module si non support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye d'ajouter un sous-modules nommé "Suivi de Mission" ayant pour description "turlututu" au module "Mission"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Impossibilité d'ajouter un sous-module si le module parent n'existe pas
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter un sous-modules nommé "Suivi de Mission" ayant pour description "turlututu" au module "Tartenpion"
        Then une erreur est levée indiquant que le module n'existe pas

    Scenario: Impossibilité d'ajouter un sous-module si le module existe déjà
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter un sous-modules nommé "Offre de Mission" ayant pour description "turlututu" au module "Mission"
        Then une erreur est levée indiquant que un module du même nom existe déjà
