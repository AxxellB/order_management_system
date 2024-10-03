<?php

namespace App\Tests\Entity;

use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class BasketProductTest extends TestCase
{
    public function testBasketProductCreation(): void
    {
        $basketProduct = new BasketProduct();
        $this->assertInstanceOf(BasketProduct::class, $basketProduct);
    }

    public function testSetAndGetQuantity(): void
    {
        $basketProduct = new BasketProduct();
        $basketProduct->setQuantity(5);

        $this->assertSame(5, $basketProduct->getQuantity());
    }

    public function testSetAndGetBasket(): void
    {
        $basketProduct = new BasketProduct();
        $basket = new Basket();
        $basketProduct->setBasket($basket);

        $this->assertSame($basket, $basketProduct->getBasket());
    }

    public function testSetAndGetProduct(): void
    {
        $basketProduct = new BasketProduct();
        $product = new Product();
        $basketProduct->setProduct($product);

        $this->assertSame($product, $basketProduct->getProduct());
    }

    public function testBasketProductRelations(): void
    {
        $basketProduct = new BasketProduct();

        $basket = $this->createMock(Basket::class);
        $product = $this->createMock(Product::class);

        $basketProduct->setBasket($basket);
        $basketProduct->setProduct($product);

        $this->assertSame($basket, $basketProduct->getBasket());
        $this->assertSame($product, $basketProduct->getProduct());
    }
}
