<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\ProductStockHistory;
use PHPUnit\Framework\TestCase;

class ProductStockHistoryTest extends TestCase
{
    public function testConstructorInitialization(): void
    {
        $product = new Product();
        $stock = 50;
        $productStockHistory = new ProductStockHistory($product, $stock);

        $this->assertEquals($product, $productStockHistory->getProductId());
        $this->assertEquals($stock, $productStockHistory->getStock());
        $this->assertInstanceOf(\DateTimeInterface::class, $productStockHistory->getTimestamp());
    }

    public function testGetAndSetTimestamp(): void
    {
        $product = new Product();
        $productStockHistory = new ProductStockHistory($product, 50);

        $timestamp = new \DateTime('2023-01-01 12:00:00');
        $productStockHistory->setTimestamp($timestamp);

        $this->assertEquals($timestamp, $productStockHistory->getTimestamp());
    }

    public function testGetAndSetProductId(): void
    {
        $product = new Product();
        $productStockHistory = new ProductStockHistory($product, 50);

        $newProduct = new Product();
        $productStockHistory->setProductId($newProduct);

        $this->assertEquals($newProduct, $productStockHistory->getProductId());
    }

    public function testGetAndSetStock(): void
    {
        $product = new Product();
        $productStockHistory = new ProductStockHistory($product, 50);

        $productStockHistory->setStock(75);
        $this->assertEquals(75, $productStockHistory->getStock());
    }
}
