<?php

namespace App\EventListener;

use App\Event\OrderPlacedEvent;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class LowStockNotifierListener
{
    private $mailerService;
    private $threshold;
    private $logger;
    private $entityManager;

    public function __construct(MailerService $mailerService, int $threshold, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->mailerService = $mailerService;
        $this->threshold = $threshold;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function onOrderPlaced(OrderPlacedEvent $event)
    {
        $order = $event->getOrder();
        $lowStockProducts = [];


        foreach ($order->getOrderProducts() as $orderProduct) {

            $product = $orderProduct->getProductEntity();

            if ($product === null) {
                $this->logger->error("OrderProduct {$orderProduct->getId()} does not have an associated Product.");
                continue;
            }

            $remainingQuantity = $product->getStockQuantity();

            if ($remainingQuantity < $this->threshold) {
                $lowStockProducts[] = [
                    'name' => $product->getName(),
                    'remainingQuantity' => $remainingQuantity,
                ];
            }
        }

        if (empty($lowStockProducts)) {
            $this->logger->info('No low stock products detected for alert.');
        } else {
            $this->mailerService->sendLowStockAlert($lowStockProducts);
            $this->logger->info('Low stock alert email sent to admin.');
        }
    }

}
