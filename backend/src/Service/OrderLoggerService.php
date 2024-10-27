<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderHistoryLogs;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderLoggerService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logOrderChange(Order $order, User $changedBy, string $changeType, array $oldValue, array $newValue): void
    {
        $log = new OrderHistoryLogs();
        $log->setRelatedOrder($order);
        $log->setUser($changedBy);
        $log->setChangeType($changeType);
        $log->setOldValue($oldValue);
        $log->setNewValue($newValue);
        $log->setChangedAt(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
