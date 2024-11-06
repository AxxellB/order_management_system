<?php

namespace App\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckExistence;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileStorageService
{
    private LocalFilesystemAdapter $adapter;
    private Filesystem $filesystem;
    private string $root;
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->root = dirname(__DIR__, 2) . '/public/uploads';
        $this->adapter = new LocalFilesystemAdapter($this->root);
        $this->filesystem = new Filesystem($this->adapter);
        $this->slugger = $slugger;
    }

    public function store(UploadedFile $file): array
    {
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return ['message' => 'Invalid file type', 'code' => Response::HTTP_UNSUPPORTED_MEDIA_TYPE];
        }

        $fileName = uniqid('file_', true) . '.' . $file->guessExtension();

        if ($this->filesystem->fileExists($fileName)) {
            return ['message' => 'File already exists', 'code' => Response::HTTP_CONFLICT];
        }

        try {
            $stream = fopen($file->getPathname(), 'r');
            $this->filesystem->writeStream($fileName, $stream);
            fclose($stream);

            return ['fileName' => $fileName, 'code' => Response::HTTP_CREATED];
        } catch (FilesystemException $e) {
            return ['message' => $e->getMessage(), 'code' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }


}

