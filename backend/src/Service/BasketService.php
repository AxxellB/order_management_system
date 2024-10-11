<?php

namespace App\Service;

use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\BasketStatus;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BasketService
{
    private EntityManagerInterface $em;
    private BasketRepository $basketRepository;

    public function __construct(EntityManagerInterface $em, BasketRepository $basketRepository)
    {
        $this->em = $em;
        $this->basketRepository = $basketRepository;
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

    public function addProductToBasket(Basket $basket, Product $product, int $quantity): void
    {
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

        $newStock = $product->getStockQuantity() - $quantity;

        if ($newStock < 0) {
            throw new \Exception('Not enough stock for this product');
        }

        $product->setStockQuantity($newStock);

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

        $currentProductQuantity = $basketProduct->getQuantity();
        $quantityProductDifference = $currentProductQuantity - $newProductQuantity;
        $updatedProductStock = $product->getStockQuantity() + $quantityProductDifference;

        if ($updatedProductStock < 0) {
            throw new \Exception('Not enough stock to fulfill the updated quantity');
        }

        $product->setStockQuantity($updatedProductStock);
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

        $returnStock = $product->getStockQuantity() + $basketProduct->getQuantity();
        $product->setStockQuantity($returnStock);

        $this->em->remove($basketProduct);
        $this->em->flush();
    }

    public function clearBasket(Basket $basket): void
    {
        foreach ($basket->getBasketProducts() as $basketProduct) {
            $product = $basketProduct->getProduct();

            $returnStock = $product->getStockQuantity() + $basketProduct->getQuantity();
            $product->setStockQuantity($returnStock);

            $this->em->remove($basketProduct);
        }

        $this->em->flush();
    }
}
