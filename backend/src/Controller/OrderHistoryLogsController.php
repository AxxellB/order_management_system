<?php

namespace App\Controller;

use App\Repository\OrderHistoryLogsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class OrderHistoryLogsController extends AbstractController
{
    private OrderHistoryLogsRepository $orderHistoryLogsRepository;

    public function __construct(OrderHistoryLogsRepository $orderHistoryLogsRepository)
    {
        $this->orderHistoryLogsRepository = $orderHistoryLogsRepository;
    }

    #[Route('/api/order-history-logs', name: 'api_order_history_logs', methods: ['GET'])]
    public function getOrderHistoryLogs(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(1000, max(1, (int)$request->query->get('limit', 15)));

        $logs = $this->orderHistoryLogsRepository->getPaginatedLogs($page, $limit);

        $totalLogs = $this->orderHistoryLogsRepository->getTotalLogCount();

        $logData = [];
        foreach ($logs as $log) {
            $logData[] = [
                'id' => $log->getId(),
                'orderId' => $log->getRelatedOrder()->getId(),
                'userId' => $log->getUser()->getEmail(),
                'changeType' => $log->getChangeType(),
                'oldValue' => $log->getOldValue(),
                'newValue' => $log->getNewValue(),
                'timestamp' => $log->getChangedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $totalPages = ceil($totalLogs / $limit);

        return new JsonResponse([
            'data' => $logData,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    #[Route('/api/order-history-logs/{id}', name: 'api_order_history_log', methods: ['GET'])]
    public function getOrderHistoryLogById(int $id): JsonResponse
    {
        $log = $this->orderHistoryLogsRepository->find($id);

        if (!$log) {
            throw new NotFoundHttpException('Log entry not found.');
        }

        $logData = [
            'id' => $log->getId(),
            'orderId' => $log->getRelatedOrder()->getId(),
            'userId' => $log->getUser()->getId(),
            'changeType' => $log->getChangeType(),
            'oldValue' => $log->getOldValue(),
            'newValue' => $log->getNewValue(),
            'timestamp' => $log->getChangedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($logData);
    }
}
