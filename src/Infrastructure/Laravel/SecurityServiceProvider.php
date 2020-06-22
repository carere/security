<?php

namespace Addworking\Security\Infrastructure\Laravel;

use Addworking\Security\Application\AuthorizationChecker;
use Addworking\Security\Application\CommandHandlers\AddModuleToEnterpriseHandler;
use Addworking\Security\Application\CommandHandlers\AddSubModuleHandler;
use Addworking\Security\Application\CommandHandlers\CreateModuleHandler;
use Addworking\Security\Application\CommandHandlers\EditModuleHandler;
use Addworking\Security\Application\CommandHandlers\RemoveModuleFromEnterpriseHandler;
use Addworking\Security\Application\CommandHandlers\RemoveModuleHandler;
use Addworking\Security\Domain\Gateways\AuthenticationGateway;
use Addworking\Security\Domain\Repositories\EnterpriseRepository;
use Addworking\Security\Domain\Repositories\MemberRepository;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Foundation\Application;
use Addworking\Security\Domain\Repositories\UserRepository;
use Addworking\Security\Infrastructure\Doctrine\EntityManagerFactory;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineUserRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineMemberRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineModuleRepository;
use Addworking\Security\Infrastructure\Doctrine\Repositories\DoctrineEnterpriseRepository;
use Addworking\Security\Infrastructure\InMemory\InMemoryAuthenticationGateway;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . "/Migrations");
    }

    public function register()
    {
        $this->app->singleton(
            EntityManagerInterface::class,
            fn($app) => EntityManagerFactory::createEntityManager([], false)
        );

        $this->registerRepositories();
        $this->registerGateways();
        $this->registerUseCases();
    }

    private function registerGateways()
    {
        $this->app->singleton(
            AuthenticationGateway::class,
            fn($app) => new InMemoryAuthenticationGateway()
        );

        $this->app->singleton(
            AuthorizationChecker::class,
            fn(Application $app) => new AuthorizationChecker(
                $app->make(MemberRepository::class),
                $app->make(EnterpriseRepository::class)
            )
        );
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

    private function registerUseCases()
    {
        $this->app->bind(
            AddModuleToEnterpriseHandler::class,
            fn(Application $app) => new AddModuleToEnterpriseHandler(
                $app->make(EnterpriseRepository::class),
                $app->make(ModuleRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );

        $this->app->bind(
            AddSubModuleHandler::class,
            fn(Application $app) => new AddSubModuleHandler(
                $app->make(ModuleRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );

        $this->app->bind(
            CreateModuleHandler::class,
            fn(Application $app) => new CreateModuleHandler(
                $app->make(ModuleRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );

        $this->app->bind(
            EditModuleHandler::class,
            fn(Application $app) => new EditModuleHandler(
                $app->make(ModuleRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );

        $this->app->bind(
            RemoveModuleFromEnterpriseHandler::class,
            fn(Application $app) => new RemoveModuleFromEnterpriseHandler(
                $app->make(ModuleRepository::class),
                $app->make(EnterpriseRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );

        $this->app->bind(
            RemoveModuleHandler::class,
            fn(Application $app) => new RemoveModuleHandler(
                $app->make(ModuleRepository::class),
                $app->make(EnterpriseRepository::class),
                $app->make(AuthenticationGateway::class),
                $app->make(AuthorizationChecker::class)
            )
        );
    }
}
