<?php

namespace App\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckExistence;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class FileStorageService
{
    private LocalFilesystemAdapter $adapter;
    private Filesystem $filesystem;

    private string $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 2) . '/public/uploads';
        $this->adapter = new LocalFilesystemAdapter($this->root);
        $this->filesystem = new Filesystem($this->adapter);
    }

    public function store(UploadedFile $file): array
    {
        $fileName = $file->getClientOriginalName();
        $fileExists = $this->filesystem->fileExists($fileName);
        if($fileExists){
            return ['message' => 'File already exists', 'code' => Response::HTTP_CONFLICT];
        }

        try{
            $stream = fopen($file->getPathname(), 'r');
            $this->filesystem->writeStream($fileName, $stream);
            fclose($stream);
            return ['filePath' => $this->root . '/' . $fileName, 'code' => Response::HTTP_CREATED];
        }catch (FilesystemException $e){
            return ['message' => $e->getMessage(), 'code' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function delete(string $filePath)
    {
        $fileExists = $this->filesystem->fileExists($filePath);
        if(!$fileExists){
            return ['message' => 'File does not exist', 'code' => Response::HTTP_NOT_FOUND];
        }

        try{
            $this->filesystem->delete($filePath);
            return ['message' => 'File has been deleted', 'code' => Response::HTTP_NO_CONTENT];
        }catch (FilesystemException | UnableToCheckExistence $exception){
            return ['message' => $exception->getMessage(), 'code' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }
}
