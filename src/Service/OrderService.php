<?php
namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\User;
use App\Service\BasketService;
use App\Enum\OrderStatus;
use App\Repository\BasketRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

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
        $order->setDeliveryAddress($user->getAddresses()[0]->getFullAddress());
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

    public function editUser(User $user, FormInterface $form): void
    {
        $selectedAddress = $form->get('selectAddress')->getData();
        if($selectedAddress) {
            $addressFormData = $form->get('addressDetails')->getData();

            $selectedAddress->setLine($addressFormData->getLine());
            $selectedAddress->setCity($addressFormData->getCity());
            $selectedAddress->setCountry($addressFormData->getCountry());
            $selectedAddress->setPostcode($addressFormData->getPostcode());
        }

        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->persist($selectedAddress);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $user = $this->userRepository->find($user->getId());
        if(!$user){
            throw new \Exception("User not found");
        }
        $user->setDeletedAt(new \DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
