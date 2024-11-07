<?php

namespace LightWeight\CLI;

use Dotenv\Dotenv;
use LightWeight\App;
use LightWeight\CLI\Commands\MakeMigration;
use LightWeight\CLI\Commands\Migrate;
use LightWeight\CLI\Commands\MigrateRollback;
use LightWeight\Config\Config;
use LightWeight\Container\Container;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Migrations\Migrator;
use Symfony\Component\Console\Application;

class CLI
{
    public static function bootstrap(string $root): self
    {
        App::$root = $root;
        Dotenv::createImmutable($root)->load();
        Config::load($root . "/config");
        foreach (config("providers.cli") as $provider) {
            (new $provider())->registerServices(Container::getInstance());
        }
        app(DatabaseDriverContract::class)->connect(
            config("database.connection"),
            config("database.host"),
            config("database.port"),
            config("database.database"),
            config("database.username"),
            config("database.password"),
        );
        singleton(
            Migrator::class,
            fn () => new Migrator(
                "$root/database/migrations",
                resourcesDirectory() . "/templates",
                app(DatabaseDriverContract::class),
            )
        );
        return new self();
    }
    public function run()
    {
        $cli = new Application("LigghWeight");
        $cli->addCommands([
            new MakeMigration(),
            new Migrate(),
            new MigrateRollback()
        ]);
        $cli->run();
    }
}
