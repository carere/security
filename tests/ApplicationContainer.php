<?php

namespace Tests;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;

class ApplicationContainer
{
    public static function getContainerAndBootEnv(): ContainerBuilder
    {
        (new Dotenv())->load(__DIR__ . '/../.env');

        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . "/../config")
        );
        $loader->load("services_{$_ENV['APP_ENV']}.yaml");
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
