<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Service\ProductStockHistoryService;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ProductStockChangeListener
{
    private ProductStockHistoryService $stockHistoryService;

    public function __construct(ProductStockHistoryService $stockHistoryService)
    {
        $this->stockHistoryService = $stockHistoryService;
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Product && $args->hasChangedField('stockQuantity')) {
            $newStock = $args->getNewValue('stockQuantity');
            $this->stockHistoryService->trackStockChange($entity, $newStock);
        }
    }
}
