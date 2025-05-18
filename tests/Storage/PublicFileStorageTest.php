<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Storage\Drivers\PublicFileStorage;
use PHPUnit\Framework\TestCase;

class PublicFileStorageTest extends TestCase
{
    protected $storageDirectory = __DIR__ . "/test-public-storage";
    protected $appUrl = "phpfr.test";
    protected $storageUri = "public-storage";
    protected $driver;
    
    protected function removeTestStorageDirectory()
    {
        if (is_dir($this->storageDirectory)) {
            $this->deleteDirectoryRecursively($this->storageDirectory);
        }
    }

    protected function deleteDirectoryRecursively($dir)
    {
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = "$dir/$item";
            if (is_dir($path)) {
                $this->deleteDirectoryRecursively($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    protected function setUp(): void
    {
        $this->removeTestStorageDirectory();
        $this->driver = new PublicFileStorage(
            $this->storageDirectory,
            $this->storageUri,
            $this->appUrl
        );
    }
    
    protected function tearDown(): void
    {
        $this->removeTestStorageDirectory();
    }
    
    /**
     * Tests storing a file with the public driver.
     */
    public function testPut()
    {
        $file = "test.txt";
        $content = "Hello, world!";
        
        $url = $this->driver->put($file, $content);
        
        // Public driver should return a URL
        $this->assertEquals("$this->appUrl/$this->storageUri/$file", $url);
        $this->assertTrue($this->driver->exists($file));
        $this->assertEquals($content, $this->driver->get($file));
    }
    
    /**
     * Tests URL functionality with public driver.
     */
    public function testUrl()
    {
        $file = "test-url.txt";
        $this->driver->put($file, "content");
        
        // URL should always be available for public driver
        $this->assertEquals("$this->appUrl/$this->storageUri/$file", $this->driver->url($file));
        
        // Even if we try to set it to private, URL should still be available
        $this->driver->setVisibility($file, "private");
        $this->assertNotNull($this->driver->url($file));
    }
    
    /**
     * Tests visibility with public driver.
     */
    public function testVisibility()
    {
        $file = "test-visibility.txt";
        $this->driver->put($file, "content");
        
        // Default visibility should be public for public driver
        $this->assertEquals("public", $this->driver->getVisibility($file));
        
        // Even if we try to set it to private, it should remain public by design
        $this->driver->put($file, "content", "private");
        $this->assertEquals("public", $this->driver->getVisibility($file));
    }
    
    /**
     * Tests file operations with public driver.
     */
    public function testFileOperations()
    {
        // Create file
        $file = "file-ops.txt";
        $content = "Test content";
        $this->driver->put($file, $content);
        
        // Verify existence
        $this->assertTrue($this->driver->exists($file));
        
        // Get content
        $this->assertEquals($content, $this->driver->get($file));
        
        // Copy file
        $copy = "file-copy.txt";
        $this->assertTrue($this->driver->copy($file, $copy));
        $this->assertTrue($this->driver->exists($copy));
        
        // Move file
        $move = "file-move.txt";
        $this->assertTrue($this->driver->move($copy, $move));
        $this->assertFalse($this->driver->exists($copy));
        $this->assertTrue($this->driver->exists($move));
        
        // Delete file
        $this->assertTrue($this->driver->delete($file));
        $this->assertFalse($this->driver->exists($file));
    }
    
    /**
     * Tests directory operations with public driver.
     */
    public function testDirectoryOperations()
    {
        // Create directory
        $dir = "test-dir";
        $this->assertTrue($this->driver->makeDirectory($dir));
        
        // Create file in directory
        $file = "$dir/test.txt";
        $this->driver->put($file, "content");
        
        // List files
        $files = $this->driver->files($dir);
        $this->assertCount(1, $files);
        $this->assertContains("test.txt", $files);
        
        // Create subdirectory
        $subdir = "$dir/subdir";
        $this->driver->makeDirectory($subdir);
        
        // List directories
        $directories = $this->driver->directories($dir);
        $this->assertCount(1, $directories);
        $this->assertContains("subdir", $directories);
        
        // Check directory emptiness
        $this->assertFalse($this->driver->directoryIsEmpty($dir));
        $this->assertTrue($this->driver->directoryIsEmpty($subdir));
        
        // Delete directory recursively
        $this->assertTrue($this->driver->deleteDirectory($dir, true));
        $this->assertFalse($this->driver->exists($dir));
    }
}
