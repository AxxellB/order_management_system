<?php

namespace App\Controller;

use App\Entity\Order;
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
    )
    {
    }

    #[Route('/orders', name: 'api_orders', methods: ['GET'])]
    public function apiViewOrders(): JsonResponse
    {
        $orders = $this->orderRepository->findAll();

        $formattedOrders = array_map(fn($order) => $this->formatOrder($order), $orders);

        return new JsonResponse($formattedOrders, Response::HTTP_OK);
    }

    #[Route('/order/{id}', name: 'api_order', methods: ['GET'])]
    public function apiViewOrder(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $formattedOrder = $this->formatOrder($order);

        return new JsonResponse($formattedOrder, Response::HTTP_OK);
    }

    // User's "My Orders" tab
    #[Route(path: '/user-orders', name: 'api_user_orders', methods: ['GET'])]
    public function apiUserOrders(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page!'], Response::HTTP_UNAUTHORIZED);
        }

        $orders = $this->orderRepository->findBy(['users' => $user]);

        if (empty($orders)) {
            return new JsonResponse(['message' => 'There are no orders for this user'], Response::HTTP_NOT_FOUND);
        }

        $formattedOrders = array_map(fn($order) => $this->formatOrder($order), $orders);

        return new JsonResponse($formattedOrders, Response::HTTP_OK);
    }

    #[Route('/orders', name: 'api_create_order', methods: ['POST'])]
    public function apiCreateOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $addressId = $data["addressId"];
        $user = $this->getUser();
        $order = $this->orderService->createOrder($user, $addressId);

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
        $statusData = $data['status'] ?? [];


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
            $this->orderService->editOrder($id, $productsData, $addressData, $statusData);

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

    private function formatOrder(Order $order): array
    {
        $formattedProducts = [];
        foreach ($order->getOrderProducts() as $orderProduct) {
            $formattedProducts[] = [
                'id' => $orderProduct->getProductEntity()->getId(),
                'name' => $orderProduct->getProductEntity()->getName(),
                'quantity' => $orderProduct->getQuantity(),
                'pricePerUnit' => $orderProduct->getPricePerUnit(),
                'subtotal' => $orderProduct->getSubtotal()
            ];
        }

        $formattedAddress = [
            'id' => $order->getAddress()->getId(),
            'line' => $order->getAddress()->getLine(),
            'line2' => $order->getAddress()->getLine2(),
            'city' => $order->getAddress()->getCity(),
            'country' => $order->getAddress()->getCountry(),
            'postcode' => $order->getAddress()->getPostcode(),
        ];

        return [
            'id' => $order->getId(),
            'userId' => $order->getUserId()->getEmail(),
            'orderDate' => $order->getOrderDate()->format('Y-m-d\TH:i:sP'),
            'totalAmount' => $order->getTotalAmount(),
            'paymentMethod' => $order->getPaymentMethod(),
            'status' => $order->getStatus(),
            'deletedAt' => $order->getDeletedAt() ? $order->getDeletedAt()->format('Y-m-d\TH:i:sP') : null,
            'orderProducts' => $formattedProducts,
            'address' => $formattedAddress,
        ];
    }
}
