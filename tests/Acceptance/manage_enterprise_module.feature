@manage-enterprise-module
Feature: Gérer les modules d'une entreprise

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
            | f1494810-ed7a-406f-8aeb-7845c4105b01 | Addworking     | Sécurité    |
            | def                                  | Entreprise n°1 | Facturation |
        And des membres d'entreprises existent
            | id  | user              | enterprise     |
            | abc | Matthieu Fravallo | Addworking     |
            | def | Jean Dupont       | Entreprise n°1 |

    Scenario: Ajouter un module à une entreprise
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter le module "Mission" à l'entreprise "Entreprise n°1"
        Then l'entreprise "Entreprise n°1" possède le module "Mission"

    Scenario: Impossibilité d'ajouter un module à une entreprise non existante
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter le module "Mission" à l'entreprise "Turlututu"
        Then une erreur est levée indiquant que l'entreprise n'existe pas

    Scenario: Impossibilité d'ajouter un module inexistant à une entreprise
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter le module "Turlututu" à l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que le module n'existe pas

    Scenario: Impossibilité d'ajouter un module à une entreprise quand on n'est pas support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye d'ajouter le module "Mission" à l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Impossibilité d'ajouter un module à une entreprise qui le possède déjà
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye d'ajouter le module "Facturation" à l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que le module est déjà possédé par une entreprise

    Scenario: Retirer un module d'une entreprise
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de retirer le module "Facturation" de l'entreprise "Entreprise n°1"
        Then l'entreprise "Entreprise n°1" ne possède plus le module "Facturation"

    Scenario: Impossibilité de retirer un module à une entreprise inexistante
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de retirer le module "Mission" de l'entreprise "Turlututu"
        Then une erreur est levée indiquant que l'entreprise n'existe pas

    Scenario: Impossibilité de retirer un module inexistant à une entreprise
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de retirer le module "Turlututu" de l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que le module n'existe pas

    Scenario: Impossibilité de retirer un module à une entreprise quand on n'est pas support
        Given je suis authentifié en tant que "Jean Dupont"
        When j'essaye de retirer le module "Facturation" de l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que le membre n'est pas support

    Scenario: Impossibilité de retirer un module d'une entreprise qui ne le possède pas
        Given je suis authentifié en tant que "Matthieu Fravallo"
        When j'essaye de retirer le module "Sécurité" de l'entreprise "Entreprise n°1"
        Then une erreur est levée indiquant que l'entreprise ne possède pas le module
