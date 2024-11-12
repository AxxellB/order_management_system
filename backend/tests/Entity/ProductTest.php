<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\OrderProduct;
use App\Entity\BasketProduct;
use App\Entity\ProductStockHistory;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testProductInitialValues(): void
    {
        $product = new Product();

        $this->assertNull($product->getId());
        $this->assertNull($product->getName());
        $this->assertNull($product->getPrice());
        $this->assertNull($product->getDescription());
        $this->assertEquals(0, $product->getStockQuantity());
        $this->assertNull($product->getDeletedAt());
        $this->assertNull($product->getImage());
        $this->assertCount(0, $product->getCategories());
        $this->assertCount(0, $product->getOrderProducts());
        $this->assertCount(0, $product->getBasketProducts());
        $this->assertCount(0, $product->getProductStockHistories());
    }

    public function testSettersAndGetters(): void
    {
        $product = new Product();

        $product->setName('Test Product');
        $this->assertEquals('Test Product', $product->getName());

        $product->setPrice('100.00');
        $this->assertEquals('100.00', $product->getPrice());

        $product->setDescription('A sample product description.');
        $this->assertEquals('A sample product description.', $product->getDescription());

        $product->setStockQuantity(50);
        $this->assertEquals(50, $product->getStockQuantity());

        $date = new \DateTimeImmutable();
        $product->setDeletedAt($date);
        $this->assertEquals($date, $product->getDeletedAt());

        $product->setImage('sample.jpg');
        $this->assertEquals('sample.jpg', $product->getImage());
    }

    public function testAddAndRemoveCategory(): void
    {
        $product = new Product();
        $category = new Category();

        $product->addCategory($category);
        $this->assertCount(1, $product->getCategories());
        $this->assertTrue($product->getCategories()->contains($category));

        $product->removeCategory($category);
        $this->assertCount(0, $product->getCategories());
        $this->assertFalse($product->getCategories()->contains($category));
    }

    public function testAddAndRemoveOrderProduct(): void
    {
        $product = new Product();
        $orderProduct = new OrderProduct();

        $product->addOrderProduct($orderProduct);
        $this->assertCount(1, $product->getOrderProducts());
        $this->assertTrue($product->getOrderProducts()->contains($orderProduct));
        $this->assertSame($product, $orderProduct->getProductEntity());

        $product->removeOrderProduct($orderProduct);
        $this->assertCount(0, $product->getOrderProducts());
        $this->assertFalse($product->getOrderProducts()->contains($orderProduct));
        $this->assertNull($orderProduct->getProductEntity());
    }

    public function testAddAndRemoveBasketProduct(): void
    {
        $product = new Product();
        $basketProduct = new BasketProduct();

        $product->addBasketProduct($basketProduct);
        $this->assertCount(1, $product->getBasketProducts());
        $this->assertTrue($product->getBasketProducts()->contains($basketProduct));
        $this->assertSame($product, $basketProduct->getProduct());

        $product->removeBasketProduct($basketProduct);
        $this->assertCount(0, $product->getBasketProducts());
        $this->assertFalse($product->getBasketProducts()->contains($basketProduct));
        $this->assertNull($basketProduct->getProduct());
    }

    public function testAddAndRemoveProductStockHistory(): void
    {
        $product = new Product();
        $productStockHistory = new ProductStockHistory($product, 100);

        $product->addProductStockHistory($productStockHistory);
        $this->assertCount(1, $product->getProductStockHistories());
        $this->assertTrue($product->getProductStockHistories()->contains($productStockHistory));
        $this->assertSame($product, $productStockHistory->getProductId());

        $product->removeProductStockHistory($productStockHistory);
        $this->assertCount(0, $product->getProductStockHistories());
        $this->assertFalse($product->getProductStockHistories()->contains($productStockHistory));
        $this->assertNull($productStockHistory->getProductId());
    }
}
