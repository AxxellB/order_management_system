<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService           $orderService,
        private readonly OrderRepository        $orderRepository,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface    $serializer, private readonly ProductRepository $productRepository
    ) {}

    // API
    #[Route('/orders', name: 'api_orders', methods: ['GET'])]
    public function apiViewOrders(): JsonResponse
    {
        $orders = $this->orderRepository->findAll();

        $data = $this->serializer->serialize($orders, 'json', ['groups' => 'order:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/order/{id}', name: 'api_order', methods: ['GET'])]
    public function apiViewOrder(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($order, 'json', ['groups' => 'order:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/orders', name: 'api_create_order', methods: ['POST'])]
    public function apiCreateOrder(): JsonResponse
    {
        $user = $this->getUser();
        $order = $this->orderService->createOrder($user);

        $data = $this->serializer->serialize($order, 'json', ['groups' => 'order:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/order/{id}', name: 'api_edit_order', methods: ['PUT'])]
    public function apiEditOrder(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);
        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $productsData = $data['products'] ?? [];
        $addressData = $data['address'] ?? [];

        $insufficientStockProducts = [];

        foreach ($productsData as $productId => $quantity) {
            $product = $this->productRepository->find($productId);

            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }

            if ($quantity > $product->getStockQuantity()) {
                $insufficientStockProducts[] = [
                    'product' => $product->getName(),
                    'currentStock' => $product->getStockQuantity(),
                    'requestedQuantity' => $quantity
                ];
            }
        }

        if (!empty($insufficientStockProducts)) {
            return new JsonResponse([
                'error' => 'Insufficient stock for the following products: ',
                'products' => $insufficientStockProducts
            ], Response::HTTP_CONFLICT);
        }

        try {
            $this->orderService->editOrder($id, $productsData, $addressData);

            return new JsonResponse(['message' => 'Order successfully updated'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/order/{id}', name: 'api_delete_order', methods: ['DELETE'])]
    public function apiDeleteOrder(Request $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $this->orderService->deleteOrder($id);

        return new JsonResponse(['message' => 'Order successfully deleted'], Response::HTTP_NO_CONTENT);
    }

    /*
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
    */
}
