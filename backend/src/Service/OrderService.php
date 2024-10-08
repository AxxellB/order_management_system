<?php
namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Enum\OrderStatus;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly OrderRepository $orderRepository,
        private readonly BasketRepository $basketRepository,
        private readonly BasketService $basketService
    ) {
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
        $order->setDeliveryAddress($user->getAddresses()[0]);
        $order->setPaymentMethod('debit card');
        $order->setStatus(OrderStatus::NEW);
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

    public function deleteOrder(int $orderId): void
    {
        $order = $this->orderRepository->find($orderId);
        if(!$order) {
            throw new \Exception("Order not found");
        }
        $order->setDeletedAt(new \DateTimeImmutable());
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
