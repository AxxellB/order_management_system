<?php

namespace App\Tests\Entity;

use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Entity\User;
use App\Enum\BasketStatus;
use PHPUnit\Framework\TestCase;

class BasketTest extends TestCase
{
    public function testBasketCreation(): void
    {
        $basket = new Basket();
        $this->assertInstanceOf(Basket::class, $basket);
        $this->assertEmpty($basket->getBasketProducts());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $basket = new Basket();
        $date = new \DateTimeImmutable('2004-02-16');
        $basket->setCreatedAt($date);

        $this->assertSame($date, $basket->getCreatedAt());
    }

    public function testSetAndGetStatus(): void
    {
        $basket = new Basket();
        $basket->setStatus(BasketStatus::ACTIVE);

        $this->assertSame(BasketStatus::ACTIVE, $basket->getStatus());
    }

    public function testSetAndGetUser(): void
    {
        $basket = new Basket();
        $user = new User();
        $basket->setUser($user);

        $this->assertSame($user, $basket->getUser());
    }

    public function testAddAndRemoveBasketProduct(): void
    {
        $basket = new Basket();
        $basketProduct = $this->createMock(BasketProduct::class);

        $basketProduct->expects($this->once())
            ->method('setBasket')
            ->with($basket);

        $basket->addBasketProduct($basketProduct);

        $this->assertCount(1, $basket->getBasketProducts());
        $this->assertTrue($basket->getBasketProducts()->contains($basketProduct));

        $basket->removeBasketProduct($basketProduct);

        $this->assertCount(0, $basket->getBasketProducts());
        $this->assertFalse($basket->getBasketProducts()->contains($basketProduct));
    }
}
