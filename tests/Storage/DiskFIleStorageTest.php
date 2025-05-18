<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Storage\Drivers\DiskFileStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DiskFIleStorageTest extends TestCase
{
    protected $storageDirectory = __DIR__ . "/test-storage";
    protected $appUrl = "phpfr.test";
    protected $storageUri = "storage";
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
        $this->driver = new DiskFileStorage(
            $this->storageDirectory, 
            $this->storageUri, 
            $this->appUrl,
            'public'
        );
    }
    protected function tearDown(): void
    {
        $this->removeTestStorageDirectory();
    }
    public static function files()
    {
        return [
            ["test.txt", "Hello World"],
            ["test/test.txt", "Hello World"],
            ["test/subdir/longer/dir/test.txt", "Hello World"],
        ];
    }

    #[DataProvider('files')]
    public function testStoresSingleFileAndCreatesParentDirectories($file, $content)
    {
        $url = $this->driver->put($file, $content);
        $path = "$this->storageDirectory/$file";
        $this->assertDirectoryExists($this->storageDirectory);
        $this->assertFileExists($path);
        $this->assertEquals($content, file_get_contents($path));
        $this->assertEquals("$this->appUrl/$this->storageUri/$file", $url);
    }
    
    public function testStoresMultipleFiles()
    {
        $f1 = "test.txt";
        $f2 = "f2.txt";
        $f3 = "foo/bar/f3.txt";
        
        foreach ([$f1, $f2, $f3] as $f) {
            $this->driver->put($f, $f);
        }
        
        foreach ([$f1, $f2, $f3] as $f) {
            $this->assertFileExists("$this->storageDirectory/$f");
            $this->assertEquals($f, file_get_contents("$this->storageDirectory/$f"));
        }
    }

    /**
     * Tests file existence checking.
     */
    public function testExists()
    {
        $file = "test-exists.txt";
        $this->driver->put($file, "content");
        
        $this->assertTrue($this->driver->exists($file));
        $this->assertFalse($this->driver->exists("nonexistent.txt"));
    }
    
    /**
     * Tests getting file content.
     */
    public function testGet()
    {
        $file = "test-get.txt";
        $content = "Hello, world!";
        $this->driver->put($file, $content);
        
        $this->assertEquals($content, $this->driver->get($file));
        $this->assertNull($this->driver->get("nonexistent.txt"));
    }
    
    /**
     * Tests deleting a file.
     */
    public function testDelete()
    {
        $file = "test-delete.txt";
        $this->driver->put($file, "content");
        
        $this->assertTrue($this->driver->exists($file));
        $this->assertTrue($this->driver->delete($file));
        $this->assertFalse($this->driver->exists($file));
        
        // Deleting a non-existent file should return false
        $this->assertFalse($this->driver->delete("nonexistent.txt"));
    }
    
    /**
     * Tests listing files.
     */
    public function testFiles()
    {
        $files = [
            "file1.txt",
            "file2.txt",
            "subdir/file3.txt",
            "subdir/nested/file4.txt"
        ];
        
        foreach ($files as $file) {
            $this->driver->put($file, "content");
        }
        
        // Test listing all files
        $allFiles = $this->driver->files();
        $this->assertCount(4, $allFiles);
        $this->assertContains("file1.txt", $allFiles);
        $this->assertContains("file2.txt", $allFiles);
        $this->assertContains("subdir/file3.txt", $allFiles);
        $this->assertContains("subdir/nested/file4.txt", $allFiles);
        
        // Test listing files in subdirectory
        $subdirFiles = $this->driver->files("subdir");
        $this->assertCount(1, $subdirFiles);
        // The returned array contains the filename without the directory prefix
        $this->assertContains("file3.txt", $subdirFiles);
        
        // Test listing files in nested subdirectory
        $nestedFiles = $this->driver->files("subdir/nested");
        $this->assertCount(1, $nestedFiles);
        $this->assertContains("file4.txt", $nestedFiles);
    }
    
    /**
     * Tests listing directories.
     */
    public function testDirectories()
    {
        $files = [
            "file1.txt",
            "subdir1/file.txt",
            "subdir2/file.txt",
            "subdir2/nested/file.txt"
        ];
        
        foreach ($files as $file) {
            $this->driver->put($file, "content");
        }
        
        // Test listing all directories
        $allDirs = $this->driver->directories();
        $this->assertCount(2, $allDirs);
        $this->assertContains("subdir1", $allDirs);
        $this->assertContains("subdir2", $allDirs);
        
        // Test listing directories in subdirectory
        $subdirDirs = $this->driver->directories("subdir2");
        $this->assertCount(1, $subdirDirs);
        $this->assertContains("nested", $subdirDirs);
    }
    
    /**
     * Tests getting file size.
     */
    public function testSize()
    {
        $file = "test-size.txt";
        $content = "Hello, world!";
        $this->driver->put($file, $content);
        
        $this->assertEquals(strlen($content), $this->driver->size($file));
    }
    
    /**
     * Tests getting file modification time.
     */
    public function testLastModified()
    {
        $file = "test-modified.txt";
        $this->driver->put($file, "content");
        
        $this->assertIsInt($this->driver->lastModified($file));
        $this->assertLessThanOrEqual(time(), $this->driver->lastModified($file));
        $this->assertGreaterThan(time() - 10, $this->driver->lastModified($file));
    }
    
    /**
     * Tests visibility functionality.
     */
    public function testVisibility()
    {
        $file = "test-visibility.txt";
        $this->driver->put($file, "content", "private");
        
        $this->assertEquals("private", $this->driver->getVisibility($file));
        
        // Change visibility to public
        $this->assertTrue($this->driver->setVisibility($file, "public"));
        $this->assertEquals("public", $this->driver->getVisibility($file));
        
        // URL should be available for public files
        $this->assertNotNull($this->driver->url($file));
        
        // Change back to private
        $this->assertTrue($this->driver->setVisibility($file, "private"));
        $this->assertEquals("private", $this->driver->getVisibility($file));
        
        // URL should be null for private files if default visibility is private
        $privateDriver = new DiskFileStorage(
            $this->storageDirectory, 
            $this->storageUri, 
            $this->appUrl,
            'private'
        );
        $privateFile = "private-file.txt";
        $privateDriver->put($privateFile, "content");
        $this->assertNull($privateDriver->url($privateFile));
    }
    
    /**
     * Tests path functionality.
     */
    public function testPath()
    {
        $file = "test-path.txt";
        $this->driver->put($file, "content");
        
        $this->assertEquals("$this->storageDirectory/$file", $this->driver->path($file));
    }
    
    /**
     * Tests directory operations.
     */
    public function testDirectoryOperations()
    {
        $dir = "test-dir";
        $subdir = "$dir/subdir";
        $file = "$subdir/test.txt";
        
        // Make directory
        $this->assertTrue($this->driver->makeDirectory($dir));
        $this->assertTrue(is_dir("$this->storageDirectory/$dir"));
        
        // Check empty directory
        $this->assertTrue($this->driver->directoryIsEmpty($dir));
        
        // Put a file in a subdirectory
        $this->driver->put($file, "content");
        $this->assertFalse($this->driver->directoryIsEmpty($dir));
        
        // Delete directory (should fail because it's not empty)
        $this->assertFalse($this->driver->deleteDirectory($dir));
        
        // Delete directory recursively
        $this->assertTrue($this->driver->deleteDirectory($dir, true));
        $this->assertFalse(is_dir("$this->storageDirectory/$dir"));
    }
    
    /**
     * Tests file copy operation.
     */
    public function testCopy()
    {
        $source = "source.txt";
        $destination = "destination.txt";
        $content = "Hello, world!";
        
        $this->driver->put($source, $content);
        $this->assertTrue($this->driver->copy($source, $destination));
        
        $this->assertTrue($this->driver->exists($source));
        $this->assertTrue($this->driver->exists($destination));
        $this->assertEquals($content, $this->driver->get($destination));
    }
    
    /**
     * Tests file move operation.
     */
    public function testMove()
    {
        $source = "source-move.txt";
        $destination = "destination-move.txt";
        $content = "Hello, world!";
        
        $this->driver->put($source, $content);
        $this->assertTrue($this->driver->move($source, $destination));
        
        $this->assertFalse($this->driver->exists($source));
        $this->assertTrue($this->driver->exists($destination));
        $this->assertEquals($content, $this->driver->get($destination));
    }
}
