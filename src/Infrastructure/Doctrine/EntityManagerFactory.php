<?php

namespace Ashiso\Security\Infrastructure\Doctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerFactory
{
    public static function createEntityManager(
        array $connection,
        bool $isDevMode
    ): EntityManagerInterface {
        return EntityManager::create(
            $connection,
            Setup::createXMLMetadataConfiguration(
                [__DIR__ . "/Mappings"],
                $isDevMode
            )
        );
    }
}
