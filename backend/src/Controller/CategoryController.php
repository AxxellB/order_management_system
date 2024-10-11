<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/categories')]
final class CategoryController extends AbstractController
{

    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }


    #[Route('/', name: 'categories_list', methods: ['GET'])]
    public function listCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $this->categoryService->getAll();

        return $this->render('category/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/api', name: 'api_categories_list', methods: ['GET'])]
    public function listCategoriesApi(SerializerInterface $serializer): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        $context = [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ];

        $jsonCategories = $serializer->serialize($categories, 'json', $context);

        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);
    }


    #[Route('/{id<\d+>}', name: 'category_products', methods: ['GET'])]
    public function listProductsByCategory(int $id, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $products = $category->getProducts();

        return $this->render('category/category_product.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/api/{id<\d+>}', name: 'api_category_products', methods: ['GET'])]
    public function listProductsByCategoryApi(int $id, SerializerInterface $serializer): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonProducts = $serializer->serialize($category->getProducts(), 'json', ['groups' => 'category:read']);
        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }


    #[Route('/view',name: 'category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $this->categoryService->getAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }


    #[Route('/api/new', name: 'api_category_new', methods: ['POST'])]
    public function newApi(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $category = $this->categoryService->createCategory($data, $validator);

        if (is_array($category) && isset($result['errors'])) {
            return new JsonResponse($result['errors'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Category created successfully', 'id' => $category->getId()], Response::HTTP_CREATED);
    }

    #[Route('/edit/{id}', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/api/edit/{id<\d+>}', name: 'api_category_edit', methods: ['PUT'])]
    public function editApi(Request $request, Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessages = $this->categoryService->getFormErrors($form);

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Category updated successfully'], Response::HTTP_OK);
    }




    #[Route('/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {

            if ($category->getProducts()->isEmpty()) {
                $category->setDeletedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $this->addFlash('success', 'Category has been deleted.');
            } else {
                $this->addFlash('error', 'The category contains a product and cannot be deleted.');
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/{id}', name: 'api_category_delete', methods: ['DELETE'])]
    public function deleteApi(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $result = $this->categoryService->deleteCategory($category);

        if ($result['status'] === 'error') {
            return new JsonResponse(['error' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Category deleted successfully'], Response::HTTP_OK);
    }

}
