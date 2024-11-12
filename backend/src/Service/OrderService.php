<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Enum\OrderStatus;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Event\OrderPlacedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface   $entityManager,
        private readonly UserRepository           $userRepository,
        private readonly OrderRepository          $orderRepository,
        private readonly BasketRepository         $basketRepository,
        private readonly BasketService            $basketService,
        private readonly ProductRepository        $productRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function validateOrder(Order $order): void
    {
        if (count($order->getOrderProducts()) === 0) {
            throw new BadRequestHttpException('An order must contain at least one product.');
        }
    }

    public function createOrder($user, $addressId, EventDispatcherInterface $eventDispatcher, $discountCode = null, $percentOff = 0): Order
    {
        $order = new Order();
        $order->setUserId($user);
        $order->setOrderDate(new \DateTime());
        $totalAmount = 0;

        $basket = $user->getBasket();
        $basketProducts = $basket->getBasketProducts();
        if (!$basketProducts) {
            throw new BadRequestHttpException('Basket is empty.');
        }

        foreach ($basketProducts as $basketProduct) {
            $productPrice = $basketProduct->getProduct()->getPrice();
            $totalAmount += $basketProduct->getQuantity() * $productPrice;
        }

        $order->setTotalAmount($totalAmount);
        $order->setPaymentMethod('Debit card');
        $order->setStatus(OrderStatus::NEW);
        $this->entityManager->persist($order);

        $userAddress = $user->getAddress($addressId);
        $deliveryAddress = new Address();
        $deliveryAddress->setUser($user);
        $deliveryAddress->setLine($userAddress->getLine());
        $deliveryAddress->setCity($userAddress->getCity());
        $deliveryAddress->setCountry($userAddress->getCountry());
        $deliveryAddress->setPostcode($userAddress->getPostcode());
        $deliveryAddress->setOrderEntity($order);
        $order->setAddress($deliveryAddress);

        $this->entityManager->persist($deliveryAddress);
        $this->entityManager->persist($order);

        foreach ($basketProducts as $basketProduct) {
            $currentProduct = $basketProduct->getProduct();
            if ($currentProduct->getStockQuantity() < $basketProduct->getQuantity()) {
                throw new \Exception('Product out of stock');
            }
            $currentProduct->setStockQuantity($currentProduct->getStockQuantity() - $basketProduct->getQuantity());

            $orderProduct = new OrderProduct();
            $orderProduct->setOrderEntity($order);
            $orderProduct->setProductEntity($currentProduct);
            $orderProduct->setQuantity($basketProduct->getQuantity());
            $orderProduct->setPricePerUnit($basketProduct->getProduct()->getPrice());
            $orderProduct->setSubtotal($basketProduct->getQuantity() * $basketProduct->getProduct()->getPrice());
            $this->entityManager->persist($orderProduct);

            $order->addOrderProduct($orderProduct);

        }

        if ($discountCode !== null) {
            $order->setDiscountCode($discountCode);
        }
        if ($percentOff > 0) {
            $order->setDiscountPercentOff($percentOff);
        }

        $this->basketService->clearBasket($basket);
        $this->entityManager->persist($basket);
        $this->entityManager->flush();

        $eventDispatcher->dispatch(new OrderPlacedEvent($order), OrderPlacedEvent::NAME);
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

        $originalStatus = $order->getStatus();

        if ($order->getStatus() === OrderStatus::CANCELLED) {
            throw new \Exception('This order cannot be modified as it is cancelled');
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

        if ($order->getStatus() === OrderStatus::CANCELLED && $originalStatus !== OrderStatus::CANCELLED) {
            foreach ($order->getOrderProducts() as $orderProduct) {
                $product = $orderProduct->getProductEntity();
                $product->setStockQuantity($product->getStockQuantity() + $orderProduct->getQuantity());
                $this->entityManager->persist($product);
            }
        } else {
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
        }

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

    public function restoreOrder(int $orderId): void
    {
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            throw new \Exception("Order not found");
        }

        $order->setDeletedAt(null);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
