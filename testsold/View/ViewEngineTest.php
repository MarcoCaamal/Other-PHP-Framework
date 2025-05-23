<?php

namespace LightWeight\Tests\View;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\View\LightEngine;
use PHPUnit\Framework\TestCase;

class ViewEngineTest extends TestCase
{
    protected string $tempDir;
    protected string $tempViewsDir;
    protected string $tempCacheDir;
    protected LightEngine $engine;

    protected function setUp(): void
    {
        // Crear directorios temporales para pruebas
        $this->tempDir = sys_get_temp_dir() . '/lightengine_test_' . uniqid();
        $this->tempViewsDir = $this->tempDir . '/views';
        $this->tempCacheDir = $this->tempDir . '/cache';

        // Crear estructura de directorios
        if (!is_dir($this->tempViewsDir)) {
            mkdir($this->tempViewsDir, 0777, true);
        }

        if (!is_dir($this->tempViewsDir . '/layouts')) {
            mkdir($this->tempViewsDir . '/layouts', 0777, true);
        }

        if (!is_dir($this->tempCacheDir)) {
            mkdir($this->tempCacheDir, 0777, true);
        }

        // Configurar el motor de vista con el directorio existente de pruebas
        $this->engine = new LightEngine(__DIR__ . '/views');
        singleton(EventDispatcherContract::class, EventDispatcher::class);
    }

    protected function tearDown(): void
    {
        // Limpiar el directorio temporal
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
        Container::deleteInstance();
    }

    private function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $path = $dir . '/' . $file;
                    is_dir($path) ? $this->removeDirectory($path) : unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    public function testRendersTemplateWithParameters()
    {
        $parameter1 = 'Test1';
        $parameter2 = 2;

        $expected = "
            <html>
                <body>
                    <h1>$parameter1</h1>
                    <h1>$parameter2</h1>
                </body>
            </html>
        ";

        $content = $this->engine->render('test', compact('parameter1', 'parameter2'), 'layout');

        $this->assertEquals(
            preg_replace("/\s/", "", $expected),
            preg_replace("/\s/", "", $content)
        );
    }

    public function testRenderWithDirectView()
    {
        file_put_contents($this->tempViewsDir . '/no_layout.php', '<h1>Vista Directa</h1>');

        $engine = new LightEngine($this->tempViewsDir);
        // Llamamos directamente a renderView en lugar de render con false
        $content = $engine->renderView('no_layout', []);

        $this->assertEquals(
            '<h1>VistaDirecta</h1>',
            preg_replace("/\s+/", "", $content)
        );
    }

    public function testThrowsExceptionForMissingView()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('View file not found');

        $this->engine->render('non_existent_view');
    }

    public function testThrowsExceptionForMissingLayout()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Layout file not found');

        // Pasamos parámetros para evitar warnings sobre variables indefinidas
        $this->engine->render('test', ['parameter1' => 'test', 'parameter2' => 'test'], 'non_existent_layout');
    }

    public function testSetCustomContentAnnotation()
    {
        // Crear vista y layout temporales con anotación personalizada
        file_put_contents($this->tempViewsDir . '/custom.php', '<h1>Hello World</h1>');
        file_put_contents($this->tempViewsDir . '/layouts/custom.php', '<html><body>{{CONTENT}}</body></html>');

        $engine = new LightEngine($this->tempViewsDir);
        $engine->setContentAnnotation('{{CONTENT}}');

        $content = $engine->render('custom', [], 'custom');

        // No eliminamos espacios en blanco dentro de las palabras
        $expected = '<html><body><h1>Hello World</h1></body></html>';
        $actual = preg_replace('/>\s+</', '><', $content);
        $actual = preg_replace('/^\s+|\s+$/', '', $actual);

        $this->assertEquals($expected, $actual);
    }

    public function testSetCustomDefaultLayout()
    {
        // Crear vista y layout temporales
        file_put_contents($this->tempViewsDir . '/simple.php', '<h1>Custom Layout</h1>');
        file_put_contents($this->tempViewsDir . '/layouts/custom_default.php', '<div>@content</div>');

        $engine = new LightEngine($this->tempViewsDir);
        $engine->setDefaultLayout('custom_default');

        $content = $engine->render('simple');

        $expected = '<div><h1>Custom Layout</h1></div>';
        $actual = preg_replace('/>\s+</', '><', $content);
        $actual = preg_replace('/^\s+|\s+$/', '', $actual);

        $this->assertEquals($expected, $actual);
    }

    public function testViewCaching()
    {
        // Crear vista y layout temporales
        $viewFile = $this->tempViewsDir . '/cache_test.php';
        file_put_contents($viewFile, '<p><?= $message ?></p>');

        $engine = new LightEngine($this->tempViewsDir);
        $engine->setCache(true, $this->tempCacheDir);

        // Asegurarnos de que el directorio de caché está vacío inicialmente
        $initialCacheFiles = glob($this->tempCacheDir . '/*.php');
        foreach ($initialCacheFiles as $file) {
            unlink($file);
        }

        // Primera renderización - debería crear el archivo de caché
        $message = 'Original Content';
        $params = ['message' => $message];
        $content1 = $engine->renderView('cache_test', $params);

        // Verificar que se ha creado un archivo de caché
        $cacheFiles = glob($this->tempCacheDir . '/*.php');
        $this->assertNotEmpty($cacheFiles, "No se creó el archivo de caché después de la primera renderización");

        // Guardar el archivo de caché y su contenido original
        $cacheFile = $cacheFiles[0];
        $originalCacheContent = file_get_contents($cacheFile);

        // Modificar el archivo de vista
        $newContent = '<p><?= strtoupper($message) ?></p>';
        file_put_contents($viewFile, $newContent);

        // Ahora vamos a hacer que el archivo de caché tenga una fecha más reciente
        // que el archivo de vista, para que se use la caché
        sleep(1);
        touch($cacheFile); // Actualizar el tiempo de modificación del archivo de caché

        // Verificar que el archivo de caché es más nuevo que el archivo de vista
        $this->assertGreaterThan(
            filemtime($viewFile),
            filemtime($cacheFile),
            "El archivo de caché debe ser más reciente que el archivo de vista"
        );

        // Segunda renderización - debería usar la caché porque la caché es más reciente
        $content2 = $engine->renderView('cache_test', $params);

        // Verificar que se usó la caché (contenido igual a la primera renderización)
        $this->assertEquals($content1, $content2, "La caché no se está utilizando correctamente");

        // Ahora hagamos que el archivo de vista sea más reciente que el caché
        sleep(1);
        touch($viewFile); // Actualizar el tiempo de modificación del archivo de vista

        // Verificar que el archivo de vista es más nuevo que el archivo de caché
        $this->assertGreaterThan(
            filemtime($cacheFile),
            filemtime($viewFile),
            "El archivo de vista debe ser más reciente que el archivo de caché"
        );

        // Tercera renderización - NO debería usar la caché porque el archivo de vista es más reciente
        $content3 = $engine->renderView('cache_test', $params);

        // El contenido debería ser diferente ya que no se usa la caché
        $this->assertNotEquals($content1, $content3, "El archivo de vista modificado debe generar un contenido diferente");
        $this->assertStringContainsString('ORIGINAL CONTENT', $content3, "El contenido debe reflejar el nuevo archivo de vista");

        // Cambiar los parámetros debería generar un contenido diferente incluso si la caché es más nueva
        touch($cacheFile); // Hacer que la caché sea más reciente de nuevo
        $newParams = ['message' => 'New Content'];
        $content4 = $engine->renderView('cache_test', $newParams);

        // El contenido debería ser diferente con parámetros diferentes
        $this->assertNotEquals($content3, $content4, "Los parámetros diferentes deberían generar un contenido diferente");
    }

    public function testAutoEscape()
    {
        $engine = new LightEngine(__DIR__ . '/views');

        // Crear vista temporal con contenido potencialmente peligroso
        $dangerousHtml = '<script>alert("XSS")</script>';
        file_put_contents($this->tempViewsDir . '/escape_test.php', '<?= $dangerous ?>');

        // Probar con escape automático activado (valor predeterminado)
        $content1 = $engine->phpFileOutput($this->tempViewsDir . '/escape_test.php', ['dangerous' => $dangerousHtml]);

        // Probar con escape automático desactivado
        $engine->setAutoEscape(false);
        $content2 = $engine->phpFileOutput($this->tempViewsDir . '/escape_test.php', ['dangerous' => $dangerousHtml]);

        // Verificar que el escape automático funciona correctamente
        $this->assertEquals(htmlspecialchars($dangerousHtml, ENT_QUOTES, 'UTF-8'), $content1);
        $this->assertEquals($dangerousHtml, $content2);
    }

    public function testSections()
    {
        // Crear vista con secciones
        $viewContent = '<?php $view->startSection("title"); ?>Page Title<?php $view->endSection(); ?>';
        $viewContent .= '<?php $view->startSection("content"); ?><p>Main content</p><?php $view->endSection(); ?>';

        file_put_contents($this->tempViewsDir . '/sections_test.php', $viewContent);

        $layoutContent = '<html><head><title><?= $view->yieldSection("title", "Default Title") ?></title></head>';
        $layoutContent .= '<body><?= $view->yieldSection("content") ?>';
        $layoutContent .= '<footer><?= $view->yieldSection("footer", "Default Footer") ?></footer></body></html>';

        file_put_contents($this->tempViewsDir . '/layouts/sections_layout.php', $layoutContent);

        $engine = new LightEngine($this->tempViewsDir);
        $content = $engine->render('sections_test', [], 'sections_layout');

        // Verificar que las secciones se renderizan correctamente
        $this->assertStringContainsString('<title>Page Title</title>', $content);
        $this->assertStringContainsString('<p>Main content</p>', $content);
        $this->assertStringContainsString('Default Footer', $content);
    }

    public function testIncludePartial()
    {
        // Crear directorio para parciales
        mkdir($this->tempViewsDir . '/partials', 0777, true);
        file_put_contents($this->tempViewsDir . '/partials/header.php', '<header>Site: <?= $siteName ?></header>');

        // Crear vista principal que incluye el parcial
        file_put_contents($this->tempViewsDir . '/main_with_partial.php', '<?= $view->include("partials/header", ["siteName" => "My Website"]) ?><div>Content</div>');

        $engine = new LightEngine($this->tempViewsDir);
        $content = $engine->renderView('main_with_partial', []);

        $this->assertStringContainsString('<header>Site: My Website</header>', $content);
        $this->assertStringContainsString('<div>Content</div>', $content);
    }

    public function testHelperMethods()
    {
        // Probar método e() (escape)
        $engine = new LightEngine(__DIR__ . '/views');
        $dangerousText = '<script>alert("danger")</script>';
        $escaped = $engine->e($dangerousText);

        $this->assertEquals(htmlspecialchars($dangerousText, ENT_QUOTES, 'UTF-8'), $escaped);

        // Probar isActive() - configurar entorno para simularlo
        $_SERVER['REQUEST_URI'] = '/products/123';

        $this->assertTrue($engine->isActive('/products'));
        $this->assertTrue($engine->isActive('/products/123'));
        $this->assertFalse($engine->isActive('/users'));

        // Restaurar entorno
        $_SERVER['REQUEST_URI'] = null;
    }
}
