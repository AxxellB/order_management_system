<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use League\Csv\Reader;
use League\Csv\Exception as CsvException;

class ProductService
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new NotFoundHttpException("Product with ID $id not found.");
        }

        return $product;
    }

    public function getFilteredAndOrderedProducts(array $criteria, array $orderBy, ?string $search = null, int $page, int $itemsPerPage): array
    {
        return $this->productRepository->findByCriteriaAndOrder($criteria, $orderBy, $search, $page, $itemsPerPage);
    }

    public function getAvailableProductsToAddToOrder(array $criteria, array $orderBy): array
    {
        return $this->productRepository->findAvailableProductsToAddToOrder($criteria, $orderBy);
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

    public function validateProductData(array $products): array
    {
        $errors = [];
        $validatedProducts = [];

        try {
            foreach ($products as $index => $product) {
                if (empty($product['productId']) || !is_numeric($product['productId'])) {
                    $errors[] = "Row ($index) is invalid or missing product's ID.";
                    continue;
                }

                if (empty($product['stockAmount']) || !is_numeric($product['stockAmount'])) {
                    $errors[] = "Row ($index) is invalid or missing stock amount.";
                    continue;
                }

                $productEntity = $this->productRepository->find((int) $product['productId']);

                if (!$productEntity) {
                    $validatedProducts[] = [
                        'productId' => $product['productId'],
                        'name' => 'N/A',
                        'currentStock' => 'N/A',
                        'stockAmount' => (int) $product['stockAmount'],
                        'isValid' => false,
                        'error' => 'Product ID not found'
                    ];
                    continue;
                }

                if ($productEntity->getDeletedAt() !== null) {
                    $validatedProducts[] = [
                        'productId' => $productEntity->getId(),
                        'name' => $productEntity->getName(),
                        'currentStock' => $productEntity->getStockQuantity(),
                        'stockAmount' => (int) $product['stockAmount'],
                        'isValid' => false,
                        'error' => 'Product is marked as deleted'
                    ];
                    continue;
                }

                $validatedProducts[] = [
                    'productId' => $productEntity->getId(),
                    'name' => $productEntity->getName(),
                    'currentStock' => $productEntity->getStockQuantity(),
                    'stockAmount' => (int) $product['stockAmount'],
                    'isValid' => true
                ];
            }
        } catch (\Exception $e) {
            error_log("Error in validateProductData: " . $e->getMessage());
            return ['validatedProducts' => [], 'errors' => ['An error occurred while validating the data.']];
        }

        return ['validatedProducts' => $validatedProducts, 'errors' => $errors];
    }

    public function bulkRestock(array $products): array
    {
        $result = ['updated' => 0, 'errors' => []];

        foreach ($products as $productData) {
            $product = $this->productRepository->find((int)$productData['productId']);

            if (!$product) {
                $result['errors'][] = "Product with ID {$productData['productId']} not found.";
                continue;
            }

            $quantityToAdd = (int)$productData['stockAmount'];
            $product->setStockQuantity($product->getStockQuantity() + $quantityToAdd);
            $this->entityManager->persist($product);
            $result['updated']++;
        }

        $this->entityManager->flush();
        return $result;
    }
}