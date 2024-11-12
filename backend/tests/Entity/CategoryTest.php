<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testInitialValues(): void
    {
        $category = new Category();

        $this->assertNull($category->getId());
        $this->assertNull($category->getName());
        $this->assertNull($category->getDeletedAt());
        $this->assertCount(0, $category->getProducts());
    }

    public function testSetName(): void
    {
        $category = new Category();
        $category->setName('Electronics');

        $this->assertEquals('Electronics', $category->getName());
    }

    public function testSetDeletedAt(): void
    {
        $category = new Category();
        $date = new \DateTimeImmutable('2023-01-01');
        $category->setDeletedAt($date);

        $this->assertEquals($date, $category->getDeletedAt());
    }

    public function testAddProduct(): void
    {
        $category = new Category();
        $product = new Product();

        $category->addProduct($product);

        $this->assertCount(1, $category->getProducts());
        $this->assertTrue($category->getProducts()->contains($product));
        $this->assertTrue($product->getCategories()->contains($category));
    }

    public function testAddProductOnlyOnce(): void
    {
        $category = new Category();
        $product = new Product();

        $category->addProduct($product);
        $category->addProduct($product);

        $this->assertCount(1, $category->getProducts());
    }

    public function testRemoveProduct(): void
    {
        $category = new Category();
        $product = new Product();

        $category->addProduct($product);
        $category->removeProduct($product);

        $this->assertCount(0, $category->getProducts());
        $this->assertFalse($category->getProducts()->contains($product));
        $this->assertFalse($product->getCategories()->contains($category));
    }
}
