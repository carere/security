<?php

namespace Tests;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApplicationContainer
{
    public static function getContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . "/../config")
        );
        $loader->load('services_dev.yaml');
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
