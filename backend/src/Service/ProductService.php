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

    public function getAllNonDeleted(): array
    {
        return $this->productRepository->findAllNonDeletedProducts();

    }

    public function getAllDeleted(): array
    {
        return $this->productRepository->findAllDeletedProducts();

    }

    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundHttpException("Product with ID $id not found.");
        }

        return $product;
    }

}