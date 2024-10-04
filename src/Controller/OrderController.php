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


    #[Route('/orders', name: 'orders')]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();
        return $this->redirectToRoute('homepage');
    }
    #[Route('/order_create', name: 'order_create')]
    public function createOrder(): Response
    {
        $user = $this->getUser();
        $this->orderService->createOrder($user);
        return $this->redirectToRoute('homepage');
    }

    #[Route('/order_edit', name: 'order_edit')]
    public function editOrder(): Response
    {
        $user = $this->getUser();
        $this->orderService->createOrder($user);
        return $this->redirectToRoute('homepage');
    }
}
