<?php

namespace App\Service;

use App\Controller\OrderController;
use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Enum\OrderStatus;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
        private readonly OrderRepository        $orderRepository,
        private readonly BasketRepository       $basketRepository,
        private readonly BasketService          $basketService,
        private readonly ProductRepository      $productRepository
    )
    {
    }

    public function createOrder($user): Order
    {
        $order = new Order();
        $order->setUserId($user);
        $order->setOrderDate(new \DateTime());
        $totalAmount = 0;
        $basket = $user->getBasket();
        $basketProducts = $basket->getBasketProducts();
        foreach ($basketProducts as $basketProduct) {
            $productPrice = $basketProduct->getProduct()->getPrice();
            $totalAmount += $basketProduct->getQuantity() * $productPrice;
        }
        $order->setTotalAmount($totalAmount);
        $order->setPaymentMethod('debit card');
        $order->setStatus(OrderStatus::NEW);
        $this->entityManager->persist($order);

        $userAddress = $user->getAddresses()[0];
        $deliveryAddress = new Address();
        $deliveryAddress->setUser($user);
        $deliveryAddress->setLine($userAddress->getLine());
        $deliveryAddress->setCity($userAddress->getCity());
        $deliveryAddress->setCountry($userAddress->getCountry());
        $deliveryAddress->setPostcode($userAddress->getPostcode());
        $deliveryAddress->setOrderEntity($order);

        $this->entityManager->persist($deliveryAddress);
        $this->entityManager->persist($order);

        foreach ($basketProducts as $basketProduct) {
            $orderProduct = new OrderProduct();
            $orderProduct->setOrderEntity($order);
            $orderProduct->setProductEntity($basketProduct->getProduct());
            $orderProduct->setQuantity($basketProduct->getQuantity());
            $orderProduct->setPricePerUnit($basketProduct->getProduct()->getPrice());
            $orderProduct->setSubtotal($basketProduct->getQuantity() * $basketProduct->getProduct()->getPrice());
            $this->entityManager->persist($orderProduct);
        }

        $this->basketService->clearBasket($basket);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();
        return $order;
    }

    /**
     * @throws \Exception
     */
    public function editOrder(int $orderId, array $orderProducts, array $orderAddress, ?string $status = null): Order
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order) {
            throw new \Exception('Order not found');
        }

        if (!in_array($order->getStatus(), [OrderStatus::NEW, OrderStatus::PROCESSING])) {
            throw new \Exception('This order cannot be modified at this stage');
        }

        $currentOrderProducts = $order->getOrderProducts();
        $totalAmount = 0;

        foreach ($orderProducts as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            $orderProduct = $currentOrderProducts->filter(function ($op) use ($product) {
                return $op->getProductEntity()->getId() === $product->getId();
            })->first();

            if ($orderProduct) {
                $oldQuantity = $orderProduct->getQuantity();
                $quantityDifference = $quantity - $oldQuantity;

                if ($quantityDifference > 0) {
                    if ($quantityDifference > $product->getStockQuantity()) {
                        throw new \Exception('Insufficient stock for ' . $product->getName());
                    }
                    $product->setStockQuantity($product->getStockQuantity() - $quantityDifference);
                } else {
                    $product->setStockQuantity($product->getStockQuantity() + abs($quantityDifference));
                }

                if ($quantity === 0) {
                    $order->removeOrderProduct($orderProduct);
                    $this->entityManager->remove($orderProduct);
                } else {
                    $orderProduct->setQuantity($quantity);
                    $orderProduct->setSubtotal($quantity * $product->getPrice());
                }
            } else {
                if ($quantity > 0) {
                    if ($quantity > $product->getStockQuantity()) {
                        throw new \Exception('Insufficient stock for ' . $product->getName());
                    }

                    $newOrderProduct = new OrderProduct();
                    $newOrderProduct->setOrderEntity($order);
                    $newOrderProduct->setProductEntity($product);
                    $newOrderProduct->setQuantity($quantity);
                    $newOrderProduct->setPricePerUnit($product->getPrice());
                    $newOrderProduct->setSubtotal($quantity * $product->getPrice());

                    $this->entityManager->persist($newOrderProduct);
                    $order->addOrderProduct($newOrderProduct);

                    $product->setStockQuantity($product->getStockQuantity() - $quantity);
                }
            }

            $totalAmount += $quantity * $product->getPrice();

            $this->entityManager->persist($product);
        }

        $order->setTotalAmount($totalAmount);

        $deliveryAddress = $order->getAddress() ?: new Address();
        if (isset($orderAddress['line'])) {
            $deliveryAddress->setLine($orderAddress['line']);
            $deliveryAddress->setLine2($orderAddress['line2']);
            $deliveryAddress->setCity($orderAddress['city']);
            $deliveryAddress->setCountry($orderAddress['country']);
            $deliveryAddress->setPostcode($orderAddress['postcode']);
            $deliveryAddress->setUser($order->getUserId());
            $deliveryAddress->setOrderEntity($order);

            $this->entityManager->persist($deliveryAddress);
            $order->setAddress($deliveryAddress);
        }

        if ($status !== null) {
            try {
                $orderStatus = OrderStatus::from($status);
                $order->setStatus($orderStatus);
                $this->entityManager->persist($order);
            } catch (\ValueError) {
                throw new \Exception('Invalid status provided');
            }
        }

        $this->entityManager->flush();

        return $order;
    }

    public function deleteOrder(int $orderId): void
    {
        $order = $this->orderRepository->find($orderId);
        if (!$order) {
            throw new \Exception("Order not found");
        }
        $order->setDeletedAt(new \DateTimeImmutable());
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
