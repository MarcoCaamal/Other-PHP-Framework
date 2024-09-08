<?php

namespace OtherPHPFramework\Tests\View;

use OtherPHPFramework\View\ViewEngine;
use PHPUnit\Framework\TestCase;

class ViewEngineTest extends TestCase
{
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

        $engine = new ViewEngine(__DIR__ . '/views');

        $content = $engine->render('test', compact('parameter1', 'parameter2'), 'layout');

        $this->assertEquals(
            preg_replace("/\s/", "", $expected),
            preg_replace("/\s/", "", $content)
        );
    }
}
