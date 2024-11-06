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


    #[Route('/{id<\d+>}/upload-image', name: 'api_product_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, Product $product, FileStorageService $fileStorageService, EntityManagerInterface $entityManager): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['message' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $responceFromService = $fileStorageService->store($file);

        if (isset($serviceResponse['message'])) {
            return new JsonResponse(['message' => $serviceResponse['message']], $serviceResponse['code']);
        }

        $product->setImage($responceFromService['fileName']);
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse([
            'fileName' => $responceFromService['fileName'],
            'message' => 'Image uploaded successfully'
        ], Response::HTTP_CREATED);
    }


    #[Route("/file/{filename}", name: "api_file_download", methods: ["GET"])]
    public function download(string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        if (!file_exists($filePath)) {
            return new JsonResponse(['message' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', mime_content_type($filePath));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);

        return $response;
    }

}
