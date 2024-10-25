<?php

namespace App\Service;

use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\BasketStatus;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BasketService
{
    private EntityManagerInterface $em;
    private BasketRepository $basketRepository;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $em, BasketRepository $basketRepository, ProductRepository $productRepository)
    {
        $this->em = $em;
        $this->basketRepository = $basketRepository;
        $this->productRepository = $productRepository;
    }

    public function createNewBasket(User $user): Basket
    {
        $basket = new Basket();
        $basket->setUser($user);
        $basket->setCreatedAt(new \DateTimeImmutable());
        $basket->setStatus(BasketStatus::ACTIVE);

        $this->em->persist($basket);
        $this->em->flush();

        return $basket;
    }

    public function getOrCreateBasket(User $user): Basket
    {
        $basket = $this->basketRepository->findOneBy([
            'status' => BasketStatus::ACTIVE,
            'user' => $user
        ]);

        if (!$basket) {
            $basket = $this->createNewBasket($user);
        }

        return $basket;
    }

    public function addProductToBasket(Basket $basket, int $productId, int $quantity): void
    {
        $product = $this->productRepository->find($productId);
        $basketProduct = $this->em->getRepository(BasketProduct::class)
            ->findOneBy(['basket' => $basket, 'product' => $product]);

        if ($basketProduct) {
            $newProductQuantity = $basketProduct->getQuantity() + $quantity;
            $basketProduct->setQuantity($newProductQuantity);
        } else {
            $basketProduct = new BasketProduct();
            $basketProduct->setBasket($basket);
            $basketProduct->setProduct($product);
            $basketProduct->setQuantity($quantity);

            $this->em->persist($basketProduct);
        }

        $this->em->flush();
    }

    public function updateProductQuantity(Basket $basket, Product $product, int $newProductQuantity): void
    {
        $basketProduct = $this->em->getRepository(BasketProduct::class)->findOneBy([
            'basket' => $basket,
            'product' => $product
        ]);

        if (!$basketProduct) {
            throw new NotFoundHttpException('Product not found in basket');
        }

        if($product->getStockQuantity() < $newProductQuantity){
            throw new NotFoundHttpException('Product out of stock');
        }

        $basketProduct->setQuantity($newProductQuantity);

        $this->em->flush();
    }

    public function removeProductFromBasket(Basket $basket, Product $product): void
    {
        $basketProduct = $this->em->getRepository(BasketProduct::class)->findOneBy([
            'basket' => $basket,
            'product' => $product
        ]);

        if (!$basketProduct) {
            throw new NotFoundHttpException('Product not found in basket');
        }

        $this->em->remove($basketProduct);
        $this->em->flush();
    }

    public function clearBasket(Basket $basket): void
    {
        foreach ($basket->getBasketProducts() as $basketProduct) {
            $this->em->remove($basketProduct);
        }

        $this->em->flush();
    }
}
