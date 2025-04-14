<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Storage\Drivers\DiskFileStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DiskFIleStorageTest extends TestCase
{
    protected $storageDirectory = __DIR__ . "/test-storage";
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
        $appUrl = "phpfr.test";
        $storageUri = "storage";
        $storage = new DiskFileStorage($this->storageDirectory, $storageUri, $appUrl);
        $url = $storage->put($file, $content);
        $path = "$this->storageDirectory/$file";
        $this->assertDirectoryExists($this->storageDirectory);
        $this->assertFileExists($path);
        $this->assertEquals($content, file_get_contents($path));
        $this->assertEquals("$appUrl/$storageUri/$file", $url);
    }
    public function testStoresMultipleFiles()
    {
        $f1 = "test.txt";
        $f2 = "f2.txt";
        $f3 = "foo/bar/f3.txt";
        $storage = new DiskFileStorage($this->storageDirectory, "test", "test");
        foreach ([$f1, $f2, $f3] as $f) {
            $storage->put($f, $f);
        }
        foreach ([$f1, $f2, $f3] as $f) {
            $this->assertFileExists("$this->storageDirectory/$f");
            $this->assertEquals($f, file_get_contents("$this->storageDirectory/$f"));
        }
    }
}
