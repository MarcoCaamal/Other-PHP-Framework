<?php

namespace LightWeight\CLI;

use Dotenv\Dotenv;
use LightWeight\App;
use LightWeight\CLI\Commands\InitApp;
use LightWeight\CLI\Commands\MakeController;
use LightWeight\CLI\Commands\MakeMigration;
use LightWeight\CLI\Commands\MakeModel;
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
        
        // Intentar cargar .env si existe, pero no fallar si no se encuentra
        if (file_exists($root . '/.env')) {
            Dotenv::createImmutable($root)->safeLoad();
        }
        
        // Cargar la configuración si existe el directorio config
        if (is_dir($root . '/config')) {
            Config::load($root . "/config");
            
            // Ejecutar proveedores de servicios CLI si están configurados
            if (function_exists('config') && config("providers.cli")) {
                foreach (config("providers.cli") as $provider) {
                    (new $provider())->registerServices(Container::getInstance());
                }
            }
        }
        
        // Intentar configurar la conexión a la base de datos solo si existe la configuración
        try {
            if (function_exists('config') && config("database")) {
                app(DatabaseDriverContract::class)->connect(
                    config("database.connection", "mysql"),
                    config("database.host", "localhost"),
                    config("database.port", 3306),
                    config("database.database", "lightweight"),
                    config("database.username", "root"),
                    config("database.password", ""),
                );
                
                // Crear directorio de migraciones si no existe
                $migrationsDir = "$root/database/migrations";
                if (!is_dir($migrationsDir)) {
                    if (!is_dir("$root/database")) {
                        mkdir("$root/database", 0755, true);
                    }
                    mkdir($migrationsDir, 0755, true);
                }
                
                // Registrar el Migrator solo si la conexión a la base de datos es exitosa
                singleton(
                    Migrator::class,
                    fn () => new Migrator(
                        $migrationsDir,
                        null, // Usar el path por defecto
                        app(DatabaseDriverContract::class),
                    )
                );
            }
        } catch (\Exception $e) {
            // Solo log de error, no detener la ejecución
            error_log("Database connection error: " . $e->getMessage());
        }
        return new self();
    }
    public function run()
    {
        $cli = new Application("LightWeight");
        $cli->addCommands([
            new MakeMigration(),
            new Migrate(),
            new MigrateRollback(),
            new MakeController(),
            new MakeModel(),
            new InitApp()
        ]);
        $cli->run();
    }
}
