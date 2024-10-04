<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(private readonly OrderService $orderService, private readonly OrderRepository $orderRepository, readonly EntityManagerInterface $em){
    }

    #[Route('/orders', name: 'orders_index', methods: ['GET'])]
    public function index(): Response
    {
        $orders = $this->orderRepository->findActiveOrders();
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/order_create', name: 'order_create')]
    public function createOrder(): Response
    {
        $user = $this->getUser();
        $this->orderService->createOrder($user);
        return $this->redirectToRoute('homepage');
    }

    #[Route('/order_edit/{id}', name: 'order_edit')]
    public function editOrder(int $id): Response
    {
        $user = $this->getUser();
        $this->orderService->createOrder($user);
        return $this->redirectToRoute('homepage');
    }

    #[Route('/order_delete/{id}', name: 'order_delete')]
    public function deleteOrder(int $id): Response
    {
        $this->orderService->deleteOrder($id);

        return $this->redirectToRoute('orders_index');
    }
}
