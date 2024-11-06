<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{

    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    #[Route('/list', name: 'api_categories_list', methods: ['GET'])]
    public function listCategoriesApi(Request $request, SerializerInterface $serializer, LoggerInterface $logger): JsonResponse
    {
        $page = $request->query->get('page');
        $limit = $request->query->get('limit');
        $filter = $request->query->get('filter');

        if ($filter !== null) {
            try {
                $Categories = $this->categoryService->getAllNonDeleted();

                $serializedCategories = $serializer->serialize($Categories, 'json', ['groups' => 'category:read']);

                return new JsonResponse($serializedCategories, Response::HTTP_OK, [], true);
            } catch (\Exception $e) {
                $logger->error('Error fetching filtered categories: ' . $e->getMessage());
                return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        if ($page !== null && $limit !== null) {
            try {
                $page = (int) $page;
                $limit = (int) $limit;

                $paginatedCategories = $this->categoryService->getAllPaginated($page, $limit);

                $serializedData = $serializer->serialize($paginatedCategories['data'], 'json', ['groups' => 'category:read']);

                $response = [
                    'data' => json_decode($serializedData, true),
                    'totalPages' => $paginatedCategories['totalPages'],
                    'currentPage' => $page
                ];

                return new JsonResponse($response, Response::HTTP_OK);
            } catch (\Exception $e) {
                $logger->error('Error fetching paginated categories: ' . $e->getMessage());
                return new JsonResponse(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse(['error' => 'Invalid parameters. Provide either page & limit or filter.'], JsonResponse::HTTP_BAD_REQUEST);
    }


    #[Route('/new', name: 'api_category_new', methods: ['POST'])]
    public function newApi(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

        $data = json_decode($request->getContent(), true);
        $category = $this->categoryService->createCategory($data, $validator);

        if (is_array($category) && isset($result['errors'])) {
            return new JsonResponse($result['errors'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Category created successfully', 'id' => $category->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', name: 'api_category_get', methods: ['GET'])]
    public function getCategoryById(int $id, SerializerInterface $serializer): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        $jsonProduct = $serializer->serialize($category, 'json', ['groups' => 'category:read']);
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }



    #[Route('/{id<\d+>}', name: 'api_category_edit', methods: ['PUT'])]
    public function editApi(Request $request, ?Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data, false);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errorMessages = $this->categoryService->getFormErrors($form);

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Category updated successfully'], Response::HTTP_OK);
    }



    #[Route('/{id}', name: 'api_category_delete', methods: ['DELETE'])]
    public function deleteApi(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

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
