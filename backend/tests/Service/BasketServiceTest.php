<?php

namespace App\Tests\Service;

use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\BasketStatus;
use App\Repository\BasketProductRepository;
use App\Repository\BasketRepository;
use App\Service\BasketService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BasketServiceTest extends TestCase
{
    private BasketService $basketService;
    private EntityManagerInterface $entityManager;
    private BasketRepository $basketRepository;
    private BasketProductRepository $basketProductRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->basketRepository = $this->createMock(BasketRepository::class);
        $this->basketProductRepository = $this->createMock(BasketProductRepository::class);

        $this->basketService = new BasketService($this->entityManager, $this->basketRepository);
    }

    public function testCreateNewBasket(): void
    {
        $user = new User();

        $basket = $this->basketService->createNewBasket($user);

        $this->assertInstanceOf(Basket::class, $basket);
        $this->assertEquals($user, $basket->getUser());
        $this->assertNotNull($basket->getCreatedAt());
        $this->assertEquals(BasketStatus::ACTIVE, $basket->getStatus());
    }

    public function testGetOrCreateBasketWhenBasketExists(): void
    {
        $user = new User();

        $existingBasket = new Basket();
        $existingBasket->setUser($user);
        $existingBasket->setStatus(BasketStatus::ACTIVE);

        $this->basketRepository->method('findOneBy')->willReturn($existingBasket);

        $basket = $this->basketService->getOrCreateBasket($user);

        $this->assertSame($existingBasket, $basket);
    }

    public function testGetOrCreateBasketWhenNoBasketExists(): void
    {
        $user = new User();

        $this->basketRepository->method('findOneBy')->willReturn(null);

        $basket = $this->basketService->getOrCreateBasket($user);

        $this->assertInstanceOf(Basket::class, $basket);
        $this->assertEquals($user, $basket->getUser());
        $this->assertNotNull($basket->getCreatedAt());
        $this->assertEquals(BasketStatus::ACTIVE, $basket->getStatus());
    }

    public function testAddProductToBasket(): void
    {
        $basket = new Basket();

        $product = new Product();

        $quantity = 2;

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $basketProduct = $this->basketService->addProductToBasket($basket, $product, $quantity);

        $this->assertInstanceOf(BasketProduct::class, $basketProduct);
        $this->assertEquals($basket, $basketProduct->getBasket());
        $this->assertEquals($product, $basketProduct->getProduct());
        $this->assertEquals($quantity, $basketProduct->getQuantity());
    }

    public function testRemoveProductFromBasket(): void
    {
        $basket = new Basket();

        $product = new Product();

        $basketProduct = new BasketProduct();
        $basketProduct->setBasket($basket);
        $basketProduct->setProduct($product);

        $this->entityManager->method('getRepository')
            ->willReturn($this->basketProductRepository);

        $this->basketProductRepository->method('findOneBy')->willReturn($basketProduct);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($basketProduct);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->basketService->removeProductFromBasket($basket, $product);
    }

    public function testUpdateProductQuantity(): void
    {
        $basket = new Basket();

        $product = new Product();

        $basketProduct = new BasketProduct();
        $basketProduct->setBasket($basket);
        $basketProduct->setProduct($product);

        $newQuantity = 5;

        $this->entityManager->method('getRepository')
            ->willReturn($this->basketProductRepository);

        $this->basketProductRepository->method('findOneBy')->willReturn($basketProduct);

        $this->entityManager->expects($this->once())->method('flush');

        $this->basketService->updateProductQuantity($basket, $product, $newQuantity);

        $this->assertEquals($newQuantity, $basketProduct->getQuantity());
    }

    public function testClearBasket(): void
    {
        $basket = new Basket();
        $basketProduct = new BasketProduct();
        $basketProduct->setBasket($basket);

        $basket->addBasketProduct($basketProduct);

        $this->entityManager->expects($this->exactly(1))
            ->method('remove')
            ->with($basketProduct);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->basketService->clearBasket($basket);

        $this->assertCount(0, $basket->getBasketProducts());
    }
}
