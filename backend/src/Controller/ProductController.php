<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/products')]
final class ProductController extends AbstractController
{

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/list', name: 'api_product_list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $status = $request->query->get('status', 'active');

        $criteria = [
            'category' => $request->query->get('category'),
            'minPrice' => is_numeric($request->query->get('minPrice')) ? (float) $request->query->get('minPrice') : null,
            'maxPrice' => is_numeric($request->query->get('maxPrice')) ? (float) $request->query->get('maxPrice') : null,
            'minStock' => is_numeric($request->query->get('minStock')) ? (int) $request->query->get('minStock') : null,
            'maxStock' => is_numeric($request->query->get('maxStock')) ? (int) $request->query->get('maxStock') : null,
        ];

        switch ($status) {
            case 'deleted':
                $criteria['deleted'] = true;
                break;
            case 'active':
            default:
                $criteria['deleted'] = false;
                break;
        }

        $products = $this->productService->getFilteredAndOrderedProducts($criteria, []);

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found matching the given criteria.'], Response::HTTP_OK);
        }

        $jsonProducts = $serializer->serialize($products, 'json', ['groups' => ['product:read']]);

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('/{id<\d+>}', name: 'api_product_by_id', methods: ['GET'])]
    public function getProductByIdApi(int $id, SerializerInterface $serializer): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'product:read']);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'api_product_new', methods: ['POST'])]
    public function newApi(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->submit($data);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessages = $this->productService->getFormErrors($form);

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product created successfully', 'id' => $product->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', name: 'api_product_edit', methods: ['PUT'])]
    public function editApi(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(ProductType::class, $product);
        $form->submit($data);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessages = $this->productService->getFormErrors($form);

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product updated successfully'], Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', name: 'api_product_delete_restore', methods: ['DELETE'])]
    public function deleteOrRestoreApi(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['action']) && $data['action'] === 'delete') {
            $product->setDeletedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return new JsonResponse(['message' => 'Product deleted successfully'], Response::HTTP_OK);
        } elseif (isset($data['action']) && $data['action'] === 'restore') {
            $product->setDeletedAt(null);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Product restored successfully'], Response::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Invalid action'], Response::HTTP_BAD_REQUEST);
    }

}
