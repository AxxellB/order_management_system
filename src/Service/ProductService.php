<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{

    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->fondAllNonDeletedProducts();

    }

    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->fondNonDeletedById($id);

        if (!$product) {
            throw new NotFoundHttpException("Product with ID $id not found.");
        }

        return $product;
    }
}