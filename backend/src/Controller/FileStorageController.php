<?php

namespace App\Controller;

use App\Service\FileStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class FileStorageController extends AbstractController
{
    private FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }


    #[Route("/file", name: "api_file_upload", methods: ["POST"])]
    public function upload(Request $request): JsonResponse
    {

        $file = $request->files->get('file');

        if(!$file){
            return new JsonResponse(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $serviceResponse = $this->fileStorageService->store($file);

        if(isset($serviceResponse['message'])) {
            return new JsonResponse(['message' => $serviceResponse['message']], $serviceResponse['code']);
        }

        return new JsonResponse(['filePath' => $serviceResponse['filePath'], $serviceResponse['code']]);
    }

    #[Route("/file/{filename}", name: "api_file_download", methods: ["GET"])]
    public function download(string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        if(!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($filePath);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

    #[Route("/file/{filename}", name: "api_file_delete", methods: ["DELETE"])]
    public function delete(string $filename): JsonResponse
    {
        $serviceResponse = $this->fileStorageService->delete($filename);

        return new JsonResponse(['message' => $serviceResponse['message']], $serviceResponse['code']);
    }
}
