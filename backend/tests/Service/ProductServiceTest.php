<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use App\Service\ProductStockHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductServiceTest extends TestCase
{
    private $productRepository;
    private $productStockHistoryService;
    private $entityManager;
    private $productService;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->productStockHistoryService = $this->createMock(ProductStockHistoryService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->productService = new ProductService(
            $this->productRepository,
            $this->productStockHistoryService,
            $this->entityManager
        );
    }

    public function testGetProductByIdSuccess()
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Sample Product');

        $this->productRepository->method('find')->willReturn($product);

        $result = $this->productService->getProductById(1);

        $this->assertInstanceOf(Product::class, $result);
    }



    public function testGetProductByIdNotFound()
    {
        $this->productRepository->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Product with ID 1 not found.');

        $this->productService->getProductById(1);
    }

    public function testGetRandomProducts()
    {
        $products = [new Product(), new Product()];
        $this->productRepository->method('findRandomProducts')->willReturn($products);

        $result = $this->productService->getRandomProducts(2);

        $this->assertCount(2, $result);
    }


    public function testValidateProductDataWithValidData()
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Test Product');
        $product->method('getStockQuantity')->willReturn(100);

        $this->productRepository->method('find')->willReturn($product);

        $products = [
            ['productId' => 1, 'stockAmount' => 20]
        ];

        $result = $this->productService->validateProductData($products);

        $this->assertCount(1, $result['validatedProducts']);
        $this->assertTrue($result['validatedProducts'][0]['isValid']);
    }

    public function testValidateProductDataWithInvalidData()
    {
        $products = [
            ['productId' => 'abc', 'stockAmount' => 'def']
        ];

        $result = $this->productService->validateProductData($products);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString("Row (0) is invalid or missing product's ID", $result['errors'][0]);
    }

    public function testValidateProductDataWithDeletedProduct()
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Deleted Product');
        $product->method('getDeletedAt')->willReturn(new \DateTimeImmutable());

        $this->productRepository->method('find')->willReturn($product);

        $products = [
            ['productId' => 1, 'stockAmount' => 20]
        ];

        $result = $this->productService->validateProductData($products);

        $this->assertFalse($result['validatedProducts'][0]['isValid']);
        $this->assertEquals('Product is marked as deleted', $result['validatedProducts'][0]['error']);
    }

    public function testBulkRestockWithValidProduct()
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getStockQuantity')->willReturn(100);

        $this->productRepository->method('find')->willReturn($product);

        $this->entityManager->expects($this->once())->method('persist')->with($product);
        $this->productStockHistoryService->expects($this->once())->method('trackStockChange');

        $products = [
            ['productId' => 1, 'stockAmount' => 20]
        ];

        $result = $this->productService->bulkRestock($products);

        $this->assertEquals(1, $result['updated']);
    }

    public function testBulkRestockWithInvalidProduct()
    {
        $this->productRepository->method('find')->willReturn(null);

        $products = [
            ['productId' => 999, 'stockAmount' => 20]
        ];

        $result = $this->productService->bulkRestock($products);

        $this->assertEquals(0, $result['updated']);
        $this->assertContains("Product with ID 999 not found.", $result['errors']);
    }
}
