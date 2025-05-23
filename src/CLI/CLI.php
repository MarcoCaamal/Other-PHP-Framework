<?php

namespace LightWeight\CLI;

use Dotenv\Dotenv;
use LightWeight\Application as App;
use LightWeight\CLI\Commands\InitApp;
use LightWeight\CLI\Commands\MakeController;
use LightWeight\CLI\Commands\MakeMigration;
use LightWeight\CLI\Commands\MakeModel;
use LightWeight\CLI\Commands\Migrate;
use LightWeight\CLI\Commands\MigrateRollback;
use LightWeight\CLI\Commands\StorageLink;
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
            Dotenv::createImmutable($root)->load();
        }
        
        // Crear el contenedor siguiendo el mismo enfoque que Application
        $container = new Container();
        $container->addDefinitions([
            'app.root' => \DI\value($root),
        ]);
        
        // Cargar definiciones básicas si existe el archivo
        if (file_exists($root . '/config/container.php')) {
            $container->addDefinitions($root . '/config/container.php');
        }
        
        // Cargar definiciones específicas para CLI
        self::collectCliDefinitions($container, $root);
        
        // Construir el contenedor (sin caché para CLI)
        $container->build();
        
        // Intentar configurar la conexión a la base de datos
        try {
            if ($container->has(Config::class)) {
                $config = $container->get(Config::class);
                
                // Ejecutar proveedores de servicios CLI
                if ($config->get('providers.cli')) {
                    foreach ($config->get('providers.cli') as $provider) {
                        (new $provider())->registerServices($container);
                    }
                }
                
                // Configurar base de datos si está disponible
                if ($container->has(DatabaseDriverContract::class)) {
                    $db = $container->get(DatabaseDriverContract::class);
                    $db->connect(
                        $config->get("database.connection", "mysql"),
                        $config->get("database.host", "localhost"),
                        $config->get("database.port", 3306),
                        $config->get("database.database", "lightweight"),
                        $config->get("database.username", "root"),
                        $config->get("database.password", ""),
                    );
                    
                    // Crear directorio de migraciones si no existe
                    $migrationsDir = "$root/database/migrations";
                    if (!is_dir($migrationsDir)) {
                        if (!is_dir("$root/database")) {
                            mkdir("$root/database", 0755, true);
                        }
                        mkdir($migrationsDir, 0755, true);
                    }
                    
                    // Registrar el Migrator
                    $container->set(Migrator::class, function ($c) use ($migrationsDir) {
                        return new Migrator(
                            migrationsDirectory: $migrationsDir,
                            templatesDirectory: null,
                            driver: $c->get(DatabaseDriverContract::class),
                            logProgress: true,
                        );
                    });
                }
            }
        } catch (\Exception $e) {
            // Solo log de error, no detener la ejecución
            error_log("CLI initialization error: " . $e->getMessage());
        }
        
        return new self();
    }
    /**
     * Recolecta definiciones específicas para el CLI
     *
     * @param Container $container
     * @param string $root
     * @return void
     */
    private static function collectCliDefinitions(Container $container, string $root): void
    {
        // Cargar definiciones de Config como prioritarias
        if (file_exists($root . '/config/providers.php')) {
            $providers = require $root . '/config/providers.php';
            
            // Cargar definiciones para providers CLI
            if (isset($providers['cli']) && is_array($providers['cli'])) {
                foreach ($providers['cli'] as $providerClass) {
                    if (class_exists($providerClass)) {
                        $provider = new $providerClass();
                        if (method_exists($provider, 'getDefinitions')) {
                            $definitions = $provider->getDefinitions();
                            if (is_array($definitions) && !empty($definitions)) {
                                $container->addDefinitions($definitions);
                            }
                        }
                    }
                }
            }
        }
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
            new InitApp(),
            new StorageLink()
        ]);
        $cli->run();
    }
}
