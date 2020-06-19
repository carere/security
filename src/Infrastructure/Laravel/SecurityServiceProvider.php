<?php

namespace Addworking\Security\Infrastructure\Laravel;

use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Foundation\Application;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Infrastructure\Doctrine\EntityManagerFactory;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineUserRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineMemberRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineModuleRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineEnterpriseRepository;

class SecurityServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            EntityManagerInterface::class,
            fn($app) => EntityManagerFactory::createEntityManager()
        );

        $this->registerRepositories();
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . "/Migrations");
    }

    private function registerRepositories()
    {
        $this->app->singleton(
            UserRepository::class,
            fn(Application $app) => new DoctrineUserRepository(
                $app->make(EntityManagerInterface::class)
            )
        );

        $this->app->singleton(
            MemberRepository::class,
            fn(Application $app) => new DoctrineMemberRepository(
                $app->make(EntityManagerInterface::class)
            )
        );

        $this->app->singleton(
            EnterpriseRepository::class,
            fn(Application $app) => new DoctrineEnterpriseRepository(
                $app->make(EntityManagerInterface::class)
            )
        );

        $this->app->singleton(
            ModuleRepository::class,
            fn(Application $app) => new DoctrineModuleRepository(
                $app->make(EntityManagerInterface::class)
            )
        );
    }
}
