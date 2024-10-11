<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{


    private CategoryRepository $categoryRepository;


    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    public function getAll(): array
    {
        return $this->categoryRepository->findAllNonDeletedCategories();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
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

    public function updateCategory(Category $category, array $data, ValidatorInterface $validator): Category|array
    {
        $category->setName($data['name'] ?? $category->getName());

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