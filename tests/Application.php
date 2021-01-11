<?php

namespace Tests;

use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ashiso\Security\Infrastructure\Doctrine\EntityManagerFactory;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Application
{
    public static function initEnv()
    {
        (new Dotenv())->load(__DIR__ . '/../.env');
    }

    public static function getContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->set(
            EntityManagerInterface::class,
            EntityManagerFactory::createEntityManager(
                [
                    'driver' => 'pdo_pgsql',
                    'user' => $_ENV['DB_USERNAME'],
                    'password' => $_ENV['DB_PASSWORD'],
                    'host' => $_ENV['DB_HOST'],
                    'port' => $_ENV['DB_PORT'],
                    'dbname' => $_ENV['DB_DATABASE'],
                ],
                in_array($_ENV['APP_ENV'], ['test', 'dev'])
            )
        );

        (new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . "/../config")
        ))->load("services_{$_ENV['APP_ENV']}.yaml");

        $containerBuilder->compile();

        return $containerBuilder;
    }

    public static function resetDatabase()
    {
        if ($_ENV['APP_ENV'] !== 'dev') {
            exec('vendor/bin/doctrine orm:schema:drop --force');
            exec('vendor/bin/doctrine orm:schema:update --force');
        }
    }
}
