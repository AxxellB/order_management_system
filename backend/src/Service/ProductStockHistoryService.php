<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductStockHistory;
use App\Repository\ProductStockHistoryRepository;

class ProductStockHistoryService
{
    private ProductStockHistoryRepository $productStockHistoryRepository;
    private bool $isTracking = false;

    public function __construct(ProductStockHistoryRepository $productStockHistoryRepository)
    {
        $this->productStockHistoryRepository = $productStockHistoryRepository;
    }

    public function trackStockChange(Product $product, int $newStock): void
    {
        if ($this->isTracking) {
            return;
        }

        $this->isTracking = true;

        try {
            $productStockHistory = new ProductStockHistory($product, $newStock);
            $this->productStockHistoryRepository->add($productStockHistory, true);
        } finally {
            $this->isTracking = false;
        }
    }
}
