<?php

namespace App\Controller;

use App\Entity\OrderHistoryLogs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OrderHistoryLogsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/order-history-logs', name: 'api_order_history_logs', methods: ['GET'])]
    public function getOrderHistoryLogs(): JsonResponse
    {
        $logs = $this->entityManager->getRepository(OrderHistoryLogs::class)->findAll();

        $logData = [];
        foreach ($logs as $log) {
            $logData[] = [
                'id' => $log->getId(),
                'userId' => $log->getUser(),
                'changeType' => $log->getChangeType(),
                'oldValue' => $log->getOldValue(),
                'newValue' => $log->getNewValue(),
                'timestamp' => $log->getChangedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($logData);
    }

    #[Route('/api/order-history-logs/{id}', name: 'api_order_history_log', methods: ['GET'])]
    public function getOrderHistoryLogById(int $id): JsonResponse
    {
        $log = $this->entityManager->getRepository(OrderHistoryLogs::class)->find($id);

        if (!$log) {
            throw new NotFoundHttpException('Log entry not found.');
        }

        $logData = [
            'id' => $log->getId(),
            'userId' => $log->getUser()->getId(),
            'changeType' => $log->getChangeType(),
            'oldValue' => $log->getOldValue(),
            'newValue' => $log->getNewValue(),
            'timestamp' => $log->getChangedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse($logData);
    }
}
