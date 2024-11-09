<?php

namespace App\Controller;

use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api')]
class AdminDashboardController extends AbstractController
{
    private OrderRepository $orderRepository;
    private OrderProductRepository $orderProductRepository;

    public function __construct(OrderRepository $orderRepository, OrderProductRepository $orderProductRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderProductRepository = $orderProductRepository;
    }

    #[Route('/total-revenue', name: 'app_admin_dashboard_total_revenue', methods: ['GET'])]
    public function totalRevenue(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        try {
            $startDate = new DateTime($startDateStr);
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format.'], Response::HTTP_BAD_REQUEST);
        }

        $revenueTrendData = $this->orderRepository->totalRevenue($startDate, $endDate);

        $totalRevenue = array_sum(array_column($revenueTrendData, 'revenue'));

        $formattedTotalRevenue = round($totalRevenue, 2);

        return new JsonResponse([
            'totalRevenue' => $formattedTotalRevenue,
            'revenueTrend' => array_map(function ($data) {
                return [
                    'date' => $data['orderDate']->format('Y-m-d'),
                    'revenue' => round((float)$data['revenue'], 2),
                ];
            }, $revenueTrendData),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }

    #[Route('/total-products-sold', name: 'app_admin_dashboard_total_products_sold', methods: ['GET'])]
    public function totalProductsSold(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        try {
            $startDate = new DateTime($startDateStr);
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format.'], Response::HTTP_BAD_REQUEST);
        }

        $totalProductsSold = $this->orderProductRepository->totalProductsSold($startDate, $endDate);

        return new JsonResponse([
            'totalProductsSold' => $totalProductsSold,
        ]);
    }

    #[Route('/total-products-sold-chart', name: 'app_admin_dashboard_total_products_sold_chart', methods: ['GET'])]
    public function totalProductsSoldChart(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        try {
            $startDate = new DateTime($startDateStr);
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format.'], Response::HTTP_BAD_REQUEST);
        }

        $salesTrendData = $this->orderProductRepository->totalProductsSoldChart($startDate, $endDate);

        return new JsonResponse([
            'salesTrend' => $salesTrendData,
        ]);
    }

    #[Route('/top-sold-products', name: 'app_admin_dashboard_top_sold_products', methods: ['GET'])]
    public function topSoldProducts(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        try {
            $startDate = new DateTime($startDateStr);
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format.'], Response::HTTP_BAD_REQUEST);
        }

        $topSoldProducts = $this->orderProductRepository->topSoldProducts($startDate, $endDate);

        return new JsonResponse([
            'topSoldProducts' => $topSoldProducts
        ]);
    }

    #[Route('/product-sales', name: 'app_admin_dashboard_product_sales', methods: ['GET'])]
    public function productSales(Request $request): JsonResponse
    {
        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        try {
            $startDate = new DateTime($startDateStr);
            $endDate = new DateTime($endDateStr);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format.'], Response::HTTP_BAD_REQUEST);
        }

        $productSales = $this->orderProductRepository->productSales($startDate, $endDate);

        return new JsonResponse([
            'productSales' => $productSales
        ]);
    }
}
