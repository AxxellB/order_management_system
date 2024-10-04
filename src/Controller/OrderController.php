<?php

namespace App\Controller;

use App\Form\OrderEditFormType;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function editOrder(int $id, Request $request): Response
    {
        $order = $this->orderRepository->find($id);
        $form = $this->createForm(OrderEditFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/orderEdit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order_delete/{id}', name: 'order_delete')]
    public function deleteOrder(int $id): Response
    {
        $this->orderService->deleteOrder($id);

        return $this->redirectToRoute('orders_index');
    }
}
