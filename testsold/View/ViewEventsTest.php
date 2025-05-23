<?php

namespace LightWeight\Tests\View;

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\ViewRenderedEvent;
use LightWeight\Events\ViewRenderingEvent;
use LightWeight\View\Contracts\ViewContract;
use LightWeight\View\LightEngine;
use PHPUnit\Framework\TestCase;

class ViewEventsTest extends TestCase
{
    private $tempViewsDir;
    private $engine;
    private $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary directory for views
        $this->tempViewsDir = sys_get_temp_dir() . '/lightweight_view_test_' . uniqid();
        mkdir($this->tempViewsDir);
        mkdir($this->tempViewsDir . '/layouts');

        // Create a simple test view
        file_put_contents(
            $this->tempViewsDir . '/test.php',
            '<h1><?php echo $title; ?></h1><p><?php echo $content; ?></p>'
        );

        // Create a simple layout
        file_put_contents(
            $this->tempViewsDir . '/layouts/main.php',
            '<html><body>@content</body></html>'
        );

        // Initialize the view engine
        $this->engine = new LightEngine($this->tempViewsDir);
        singleton(ViewContract::class, $this->engine);

        // Create a mock event dispatcher
        $this->dispatcher = singleton(EventDispatcherContract::class, EventDispatcher::class);

        // Define the global event function for testing purposes
        if (!function_exists('event')) {
            eval('
            function event($event, $payload = []) {
                global $GLOBALS;
                if (!isset($GLOBALS["eventDispatcher"])) {
                    return;
                }
                $GLOBALS["eventDispatcher"]->dispatch($event, $payload);
            }
            ');
        }

        // Store the dispatcher in globals so it can be accessed by the event function
        $GLOBALS['eventDispatcher'] = $this->dispatcher;
    }

    protected function tearDown(): void
    {
        // Clean up the temporary directory
        array_map('unlink', glob($this->tempViewsDir . '/*.php'));
        array_map('unlink', glob($this->tempViewsDir . '/layouts/*.php'));
        rmdir($this->tempViewsDir . '/layouts');
        rmdir($this->tempViewsDir);

        // Clean up globals
        unset($GLOBALS['eventDispatcher']);

        parent::tearDown();
    }

    public function testViewRenderingEventIsFired()
    {
        $viewName = '';
        $viewParams = [];
        $viewLayout = null;
        $eventFired = false;

        // Register a listener for the view.rendering event
        $this->dispatcher->listen('view.rendering', function ($event) use (&$viewName, &$viewParams, &$viewLayout, &$eventFired) {
            $eventFired = true;
            $viewName = $event->getView();
            $viewParams = $event->getParams();
            $viewLayout = $event->getLayout();
        });

        // Render a view
        $this->engine->render('test', [
            'title' => 'Test Title',
            'content' => 'This is a test'
        ]);

        // Assert that the event was fired
        $this->assertTrue($eventFired);
        $this->assertEquals('test', $viewName);
        $this->assertArrayHasKey('title', $viewParams);
        $this->assertArrayHasKey('content', $viewParams);
        $this->assertEquals('Test Title', $viewParams['title']);
        $this->assertEquals('This is a test', $viewParams['content']);
        $this->assertNull($viewLayout);
    }

    public function testViewRenderedEventIsFired()
    {
        $viewName = '';
        $viewParams = [];
        $viewLayout = null;
        $viewContent = '';
        $eventFired = false;

        // Register a listener for the view.rendered event
        $this->dispatcher->listen('view.rendered', function ($event) use (&$viewName, &$viewParams, &$viewLayout, &$viewContent, &$eventFired) {
            $eventFired = true;
            $viewName = $event->getView();
            $viewParams = $event->getParams();
            $viewLayout = $event->getLayout();
            $viewContent = $event->getContent();
        });

        // Render a view
        $result = $this->engine->render('test', [
            'title' => 'Test Title',
            'content' => 'This is a test'
        ]);

        // Assert that the event was fired
        $this->assertTrue($eventFired);
        $this->assertEquals('test', $viewName);
        $this->assertArrayHasKey('title', $viewParams);
        $this->assertArrayHasKey('content', $viewParams);
        $this->assertEquals('Test Title', $viewParams['title']);
        $this->assertEquals('This is a test', $viewParams['content']);
        $this->assertNull($viewLayout);
        $this->assertStringContainsString('<h1>Test Title</h1>', $viewContent);
        $this->assertStringContainsString('<p>This is a test</p>', $viewContent);
        $this->assertStringContainsString('<html><body>', $viewContent);
        $this->assertStringContainsString('</body></html>', $viewContent);

        // The rendered content should match the result
        $this->assertEquals($result, $viewContent);
    }

    public function testViewRenderedEventWithNoLayout()
    {
        $viewContent = '';
        $eventFired = false;

        // Register a listener for the view.rendered event
        $this->dispatcher->listen('view.rendered', function ($event) use (&$viewContent, &$eventFired) {
            $eventFired = true;
            $viewContent = $event->getContent();
        });

        // Render a view without a layout
        $result = $this->engine->render('test', [
            'title' => 'No Layout',
            'content' => 'This is without layout'
        ], false);

        // Assert that the event was fired
        $this->assertTrue($eventFired);
        $this->assertStringContainsString('<h1>No Layout</h1>', $viewContent);
        $this->assertStringContainsString('<p>This is without layout</p>', $viewContent);
        $this->assertStringNotContainsString('<html><body>', $viewContent);
        $this->assertStringNotContainsString('</body></html>', $viewContent);

        // The rendered content should match the result
        $this->assertEquals($result, $viewContent);
    }
}
