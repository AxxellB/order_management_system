<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{

    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundHttpException("Product with ID $id not found.");
        }

        return $product;
    }

    public function getFilteredAndOrderedProducts(array $criteria, array $orderBy, ?string $search = null, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->productRepository->findByCriteriaAndOrder($criteria, $orderBy, $search, $page, $itemsPerPage);
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