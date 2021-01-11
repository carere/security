<?php

namespace Ashiso\Security\Infrastructure\Laravel;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Foundation\Application;
use Ashiso\Security\Application\AuthorizationChecker;
use Ashiso\Security\Domain\Repositories\UserRepository;
use Ashiso\Security\Domain\Repositories\MemberRepository;
use Ashiso\Security\Domain\Gateways\AuthenticationGateway;
use Ashiso\Security\Domain\Repositories\EnterpriseRepository;
use Ashiso\Security\Infrastructure\Doctrine\EntityManagerFactory;
use Ashiso\Security\Application\CommandHandlers\EditModuleHandler;
use Ashiso\Security\Application\CommandHandlers\AddSubModuleHandler;
use Ashiso\Security\Application\CommandHandlers\CreateModuleHandler;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleHandler;
use Ashiso\Security\Application\CommandHandlers\AddModuleToEnterpriseHandler;
use Ashiso\Security\Infrastructure\Doctrine\Repositories\DoctrineUserRepository;
use Ashiso\Security\Application\CommandHandlers\RemoveModuleFromEnterpriseHandler;
use Ashiso\Security\Infrastructure\Doctrine\Repositories\DoctrineMemberRepository;
use Ashiso\Security\Infrastructure\Doctrine\Repositories\DoctrineModuleRepository;
use Ashiso\Security\Infrastructure\Doctrine\Repositories\DoctrineEnterpriseRepository;
use Ashiso\Security\Infrastructure\Laravel\Gateways\LaravelAuthenticationGateway;

class SecurityServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . "/Migrations");
        $this->publishes(
            [
                __DIR__ . "/Config/security.php" => config_path('security.php'),
            ],
            'security'
        );
    }

    public function register()
    {
        $this->app->singleton(
            EntityManagerInterface::class,
            fn($app) => EntityManagerFactory::createEntityManager(
                [
                    'driver' => Config::get('security.database.driver'),
                    'user' => Config::get('security.database.user'),
                    'password' => Config::get('security.database.password'),
                    'host' => Config::get('security.database.host'),
                    'port' => Config::get('security.database.port'),
                    'dbname' => Config::get('security.database.dbname'),
                ],
                in_array(Config::get('security.env'), ['local', 'test'])
            )
        );

        $this->registerRepositories();
        $this->registerGateways();
        $this->registerUseCases();
    }

    private function registerGateways()
    {
        $this->app->singleton(
            AuthenticationGateway::class,
            fn(Application $app) => new LaravelAuthenticationGateway(
                $app->make(UserRepository::class)
            )
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
