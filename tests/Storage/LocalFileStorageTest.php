<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Storage\Drivers\LocalFileStorage;
use PHPUnit\Framework\TestCase;

class LocalFileStorageTest extends TestCase
{
    protected $storageDirectory = __DIR__ . "/test-local-storage";
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
        $this->driver = new LocalFileStorage($this->storageDirectory);
    }
    
    protected function tearDown(): void
    {
        $this->removeTestStorageDirectory();
    }
    
    /**
     * Tests storing a file with the local driver.
     */
    public function testPut()
    {
        $file = "test.txt";
        $content = "Hello, world!";
        
        $path = $this->driver->put($file, $content);
        
        // Local driver should return a path not a URL
        $this->assertEquals($file, $path);
        $this->assertTrue($this->driver->exists($file));
        $this->assertEquals($content, $this->driver->get($file));
    }
    
    /**
     * Tests URL functionality with local driver.
     */
    public function testUrl()
    {
        $file = "test-url.txt";
        $this->driver->put($file, "content");
        
        // URL should always be null for local driver
        $this->assertNull($this->driver->url($file));
    }
    
    /**
     * Tests visibility with local driver.
     */
    public function testVisibility()
    {
        $file = "test-visibility.txt";
        $this->driver->put($file, "content");
        
        // Default visibility should be private for local driver
        $this->assertEquals("private", $this->driver->getVisibility($file));
        
        // Even if we try to set it to public, it should remain private by design
        $this->driver->put($file, "content", "public");
        $this->assertEquals("private", $this->driver->getVisibility($file));
    }
    
    /**
     * Tests file operations with local driver.
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
     * Tests directory operations with local driver.
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
