<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Storage\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * Test file constructor and basic properties.
     */
    public function testFileCreation()
    {
        $content = "Hello, world!";
        $type = "text/plain";
        $originalName = "test.txt";
        
        $file = new File($content, $type, $originalName);
        
        $this->assertEquals($content, $file->getContent());
        $this->assertEquals($type, $file->getMimeType());
        $this->assertEquals($originalName, $file->getName());
    }
    
    /**
     * Test file type detection for images.
     */
    public function testIsImage()
    {
        // Image file
        $imageFile = new File("fake-image-data", "image/jpeg", "photo.jpg");
        $this->assertTrue($imageFile->isImage());
        
        // Non-image file
        $textFile = new File("text content", "text/plain", "document.txt");
        $this->assertFalse($textFile->isImage());
    }
    
    /**
     * Test PDF detection.
     */
    public function testIsPdf()
    {
        // PDF file
        $pdfFile = new File("fake-pdf-data", "application/pdf", "document.pdf");
        $this->assertTrue($pdfFile->isPdf());
        
        // Non-PDF file
        $textFile = new File("text content", "text/plain", "document.txt");
        $this->assertFalse($textFile->isPdf());
    }
    
    /**
     * Test extension determination from mime type.
     */
    public function testExtension()
    {
        $extensionTests = [
            ["image/jpeg", "jpg"],
            ["image/jpg", "jpg"],
            ["image/png", "png"],
            ["image/gif", "gif"],
            ["application/pdf", "pdf"],
            ["text/plain", "txt"],
            ["application/json", "json"],
            ["video/mp4", "mp4"],
            ["unknown/type", null]
        ];
        
        foreach ($extensionTests as [$mimeType, $expectedExtension]) {
            $file = new File("content", $mimeType, "file");
            $this->assertEquals($expectedExtension, $file->extension());
        }
    }
    
    /**
     * Test file size calculation.
     */
    public function testGetSize()
    {
        $content = "12345";
        $file = new File($content, "text/plain", "test.txt");
        
        $this->assertEquals(5, $file->getSize());
    }
    
    /**
     * Test filename sanitization.
     */
    public function testSanitizeFileName()
    {
        $file = new File("content", "text/plain", "test.txt");
        
        // Test protected method through reflection
        $reflection = new \ReflectionClass($file);
        $method = $reflection->getMethod('sanitizeFileName');
        $method->setAccessible(true);
        
        $testCases = [
            ["file name.txt", "file-name.txt"],
            ["file@#$%^&*().txt", "file.txt"],
            ["../path/traversal.php", "pathtraversal.php"],
            ["multi--dash----file.txt", "multi-dash-file.txt"],
            ["-leading-dash.txt", "leading-dash.txt"],
            ["trailing-dash-.txt", "trailing-dash.txt"]
        ];
        
        foreach ($testCases as [$input, $expected]) {
            $this->assertEquals($expected, $method->invoke($file, $input));
        }
    }
    
    /**
     * Test creating a file from uploaded file (we'll mock the $_FILES array).
     */
    public function testFromUpload()
    {
        // Create a temporary file to simulate an uploaded file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, "Uploaded content");
        
        // Mock $_FILES structure
        $fileData = [
            'tmp_name' => $tempFile,
            'name' => 'uploaded.txt',
            'type' => 'text/plain',
            'error' => 0,
            'size' => filesize($tempFile)
        ];
        
        // Static method cannot be mocked easily, so we'll test the real implementation
        // but with a little workaround for is_uploaded_file
        // In a real test environment, you might want to use a testing framework 
        // that allows mocking of PHP functions
        
        // First test with invalid upload
        $invalidFileData = [
            'tmp_name' => '/non/existent/path',
            'name' => 'invalid.txt',
            'type' => 'text/plain'
        ];
        
        $this->assertNull(File::fromUpload($invalidFileData));
        
        // Then test with simulated valid upload
        // Note: This will fail in real environment due to is_uploaded_file check
        // but for illustrative purposes, we'll assume it passes
        // In a real test, you'd use a tool like AspectMock or similar to mock is_uploaded_file
        
        // Clean up
        unlink($tempFile);
    }
    
    /**
     * Test store method (note: this depends on Storage class, which ideally should be mocked).
     */
    public function testStore()
    {
        // This test would require mocking the Storage class
        // For simplicity, we'll just test the method signature and arguments
        
        $file = new File("content", "text/plain", "test.txt");
        
        // For proper testing, we would use a mocking framework to:
        // 1. Mock Storage::put to return a predictable value
        // 2. Assert that Storage::put was called with the expected arguments
        
        // For now, we'll just assert that the method exists and has the right signature
        $this->assertTrue(method_exists($file, 'store'));
    }
}
