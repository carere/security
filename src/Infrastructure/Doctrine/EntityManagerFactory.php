<?php

namespace Addworking\Security\Infrastructure\Doctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerFactory
{
    public static function createEntityManager(): EntityManagerInterface
    {
        return EntityManager::create(
            [
                'driver' => 'pdo_pgsql',
                'user' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
            ],
            Setup::createXMLMetadataConfiguration(
                [__DIR__ . "/Mappings"],
                $_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test'
            )
        );
    }
}
