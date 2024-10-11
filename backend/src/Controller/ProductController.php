<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/products')]
final class ProductController extends AbstractController
{

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/',name: 'product_index', methods: ['GET'])]
    public function getProducts(Request $request): Response
    {
        $status = $request->query->get('status', 'active');

        if($status === 'deleted'){
            $products = $this->productService->getAllDeleted();
        } else{
            $products = $this->productService->getAllNonDeleted();

        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'status' => $status,
        ]);
    }

    #[Route('/api', name: 'api_product_list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $criteria = [
            'category' => $request->query->get('category'),
            'minPrice' => is_numeric($request->query->get('minPrice')) ? (float) $request->query->get('minPrice') : null,
            'maxPrice' => is_numeric($request->query->get('maxPrice')) ? (float) $request->query->get('maxPrice') : null,
            'minStock' => is_numeric($request->query->get('minStock')) ? (int) $request->query->get('minStock') : null,
            'maxStock' => is_numeric($request->query->get('maxStock')) ? (int) $request->query->get('maxStock') : null,
        ];

        $orderBy = [];
        $orderParameter = $request->query->get('order');
        $direction = strtolower($request->query->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        switch ($orderParameter) {
            case 'name':
                $orderBy['name'] = $direction;
                break;
            case 'price':
                $orderBy['price'] = $direction;
                break;
            case 'stock':
                $orderBy['stockQuantity'] = $direction;
                break;
            default:
                $orderBy['name'] = $direction;
                break;
        }

        $products = $this->productService->getFilteredAndOrderedProducts($criteria, $orderBy);


        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found matching the given criteria.'], Response::HTTP_OK);
        }

        $context = [
            'groups' => ['product:read'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ];

        try {
            $jsonProducts = $serializer->serialize($products, 'json', $context);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while processing the products.', 'details' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    #[Route('/{id<\d+>}', name: 'product_by_id', methods: ['GET'])]
    public function GetProductById(int $id): Response
    {
        $productById = $this->productService->getProductById($id);

        $isDeleted = ($productById->getDeletedAt() !== null);

        return $this->render('product/show.html.twig', [
            'product' => $productById,
            'isDeleted' => $isDeleted,
        ]);
    }

    #[Route('/api/{id<\d+>}', name: 'api_product_by_id', methods: ['GET'])]
    public function getProductByIdApi(int $id, SerializerInterface $serializer): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'product:read']);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/api/new', name: 'api_product_new', methods: ['POST'])]
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



    #[Route('/edit/{id<\d+>}', name: 'product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/api/edit/{id<\d+>}', name: 'api_product_edit', methods: ['PUT'])]
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

    #[Route('/{id<\d+>}', name: 'product_delete_restore', methods: ['POST'])]
    public function deleteOrRestore(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {

            $product->setDeletedAt(new \DateTimeImmutable());

            $entityManager->flush();
        }
        else if ($this->isCsrfTokenValid('restore'.$product->getId(), $request->getPayload()->getString('_token'))) {

            $product->setDeletedAt(null);

            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/{id<\d+>}', name: 'api_product_delete_restore', methods: ['DELETE'])]
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
