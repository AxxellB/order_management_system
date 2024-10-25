<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{


    private CategoryRepository $categoryRepository;
    private $formFactory;
    private $entityManager;


    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    public function getAllNonDeleted(): array
    {
       return $this->categoryRepository->findAllNonDeleted();

    }
    public function getAllPaginated(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        $categoriesData = $this->categoryRepository->findAllNonDeletedCategoriesWithPagination($offset, $limit);

        return [
            'data' => $categoriesData['categories'],
            'totalPages' => ceil($categoriesData['totalCount'] / $limit),
        ];
    }


    public function getCategoryById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        return $category;
    }


    public function createCategory(array $data, ValidatorInterface $validator): Category|array
    {
        $category = new Category();
        $category->setName($data['name'] ?? '');

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ['errors' => $errorMessages];
        }

        return $category;
    }


    public function deleteCategory(Category $category): array
    {
        if (!$category->getProducts()->isEmpty()) {
            return ['status' => 'error', 'message' => 'The category contains products and cannot be deleted.'];
        }

        $category->setDeletedAt(new \DateTimeImmutable());

        return ['status' => 'success', 'message' => 'Category deleted successfully.'];
    }

    public function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $propertyPath = $error->getOrigin()->getName();
            $errors[$propertyPath][] = $error->getMessage();
        }

        return $errors;
    }


}