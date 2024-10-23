<?php
namespace App\Tests\Service;

use App\Service\FileStorageService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
class FileStorageServiceTest extends KernelTestCase
{
    private $filesystem;
    private FileStorageService $fileStorageService;
    private string $tempFilePath;
    private string $uploadedFilePath;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->fileStorageService = self::getContainer()->get(FileStorageService::class);

        $this->tempFilePath = tempnam(sys_get_temp_dir(), 'testfile') . '.txt';
        file_put_contents($this->tempFilePath, 'test content');
    }

    public function testSuccessfulFileUpload()
    {
        $file = $this->createMock(UploadedFile::class);
        $file->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('testfile.txt');
        $file->expects($this->once())
            ->method('getPathname')
            ->willReturn($this->tempFilePath);

        $result = $this->fileStorageService->store($file);
        $this->uploadedFilePath = $result['filePath'];

        $expectedFilePath = dirname(__DIR__, 2) . '/public/uploads/testfile.txt';

        $this->assertArrayHasKey('filePath', $result);
        $this->assertEquals($expectedFilePath, $result['filePath']);
        $this->assertEquals(Response::HTTP_CREATED, $result['code']);
    }

    public function testFileUploadWithExistingFile(){
        $file = $this->createMock(UploadedFile::class);
        $file->expects($this->any())
            ->method('getClientOriginalName')
            ->willReturn('testfile.txt');

        $file->expects($this->any())
            ->method('getPathname')
            ->willReturn($this->tempFilePath);

        $storedFile = $this->fileStorageService->store($file);

        $this->filesystem->method('fileExists')->willReturn(true);

        $result = $this->fileStorageService->store($file);

        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertEquals('File already exists', $result['message']);
        $this->assertEquals(Response::HTTP_CONFLICT, $result['code']);

        $this->uploadedFilePath = $storedFile['filePath'];
    }

    protected function tearDown(): void
    {
        if (isset($this->tempFilePath) && file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
        }

        if (isset($this->uploadedFilePath) && file_exists($this->uploadedFilePath)) {
            unlink($this->uploadedFilePath);
        }

        parent::tearDown();
    }
}

