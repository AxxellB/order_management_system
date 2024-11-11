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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Knp\Snappy\Pdf;


#[Route(path: '/api')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService           $orderService,
        private readonly OrderRepository        $orderRepository,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface    $serializer,
        private readonly ProductRepository      $productRepository,
        private readonly Pdf                    $snappyPdf,
        private readonly Environment            $twig
    )
    {
    }

    #[Route('/orders', name: 'api_orders', methods: ['GET'])]
    public function apiViewOrders(Request $request): JsonResponse
    {
        $status = $request->query->get('status', 'active');
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = max(1, (int)$request->query->get('itemsPerPage', 10));
        $search = $request->query->get('search', '');

        $totalOrders = $this->orderRepository->countOrdersByStatusAndSearch($status, $search);

        $orders = $this->orderRepository->findOrdersByStatusAndSearch($status, $search, $page, $itemsPerPage);

        if (!$orders) {
            return new JsonResponse([], Response::HTTP_OK);
        }

        $formattedOrders = array_map(fn($order) => $this->formatOrder($order), $orders);

        return new JsonResponse([
            'orders' => $formattedOrders,
            'totalItems' => $totalOrders,
            'currentPage' => $page,
            'itemsPerPage' => $itemsPerPage
        ], Response::HTTP_OK);
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

    // User's "My Orders" tab

    #[Route('/order/{id}', name: 'api_order', methods: ['GET'])]
    public function apiViewOrder(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return new JsonResponse([], Response::HTTP_OK);
        }

        $formattedOrder = $this->formatOrder($order);

        return new JsonResponse($formattedOrder, Response::HTTP_OK);
    }

    #[Route(path: '/user-orders', name: 'api_user_orders', methods: ['GET'])]
    public function apiUserOrders(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page!'], Response::HTTP_UNAUTHORIZED);
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = max(1, (int)$request->query->get('itemsPerPage', 10));
        $offset = ($page - 1) * $itemsPerPage;

        $totalOrders = $this->orderRepository->count(['users' => $user]);

        $orders = $this->orderRepository->findBy(
            ['users' => $user],
            ['orderDate' => 'DESC'],
            $itemsPerPage,
            $offset
        );

        if (empty($orders)) {
            return new JsonResponse(['message' => 'There are no orders for this user'], Response::HTTP_NOT_FOUND);
        }

        $formattedOrders = array_map(fn($order) => $this->formatOrder($order), $orders);

        return new JsonResponse([
            'orders' => $formattedOrders,
            'totalItems' => $totalOrders
        ], Response::HTTP_OK);
    }

    #[Route('/orders', name: 'api_create_order', methods: ['POST'])]
    public function apiCreateOrder(Request $request, EventDispatcherInterface $eventDispatcher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data["address"])) {
            return new JsonResponse(["message" => "Address is required"], Response::HTTP_BAD_REQUEST);
        }
        
        $addressId = $data["addressId"];
        $user = $this->getUser();

        if (isset($data['discountCode'], $data['percentOff']) && $data['discountCode'] != "" && $data['percentOff'] != "") {
            $order = $this->orderService->createOrder($user, $addressId, $eventDispatcher, $data['discountCode'], $data['percentOff']);
        } else {
            $order = $this->orderService->createOrder($user, $addressId, $eventDispatcher);
        }
        $data = $this->serializer->serialize($order, 'json', ['groups' => 'order:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/order/{id}', name: 'api_edit_order', methods: ['PUT'])]
    public function apiEditOrder(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

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
            $this->orderService->validateOrder($order);

            $this->orderService->editOrder($id, $productsData, $addressData, $statusData);

            return new JsonResponse(['message' => 'Order successfully updated'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/order/{id}', name: 'api_delete_or_restore_order', methods: ['DELETE'])]
    public function apiDeleteOrRestoreOrder(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ADMIN_ACCESS');

        $order = $this->orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        if ($order->getDeletedAt()) {
            try {
                $this->orderService->restoreOrder($id);

                return new JsonResponse(['message' => 'Order successfully restored'], Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->orderService->deleteOrder($id);

        return new JsonResponse(['message' => 'Order successfully deleted'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/invoice/download/{orderId}', name: 'invoice_download', methods: ['GET'])]
    public function downloadInvoice(int $orderId): Response
    {
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        $html = $this->twig->render('invoice/invoice.html.twig', [
            'order' => $order
        ]);

        $pdfContent = $this->snappyPdf->getOutputFromHtml($html);

        $response = new Response($pdfContent);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'invoice_' . $orderId . '.pdf'
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
