<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class OrderProductTest extends TestCase
{
    public function testOrderProductCreation(): void
    {
        $orderProduct = new OrderProduct();
        $this->assertInstanceOf(OrderProduct::class, $orderProduct);
    }

    public function testSetAndGetQuantity(): void
    {
        $orderProduct = new OrderProduct();
        $quantity = 5;

        $orderProduct->setQuantity($quantity);

        $this->assertSame($quantity, $orderProduct->getQuantity());
    }

    public function testSetAndGetPricePerUnit(): void
    {
        $orderProduct = new OrderProduct();
        $pricePerUnit = '18.00';

        $orderProduct->setPricePerUnit($pricePerUnit);

        $this->assertSame($pricePerUnit, $orderProduct->getPricePerUnit());
    }

    public function testSetAndGetSubtotal(): void
    {
        $orderProduct = new OrderProduct();
        $subtotal = '90.00';

        $orderProduct->setSubtotal($subtotal);

        $this->assertSame($subtotal, $orderProduct->getSubtotal());
    }

    public function testSetAndGetOrderEntity(): void
    {
        $orderProduct = new OrderProduct();
        $order = new Order();

        $orderProduct->setOrderEntity($order);

        $this->assertSame($order, $orderProduct->getOrderEntity());
    }

    public function testSetAndGetProductEntity(): void
    {
        $orderProduct = new OrderProduct();
        $product = new Product();

        $orderProduct->setProductEntity($product);

        $this->assertSame($product, $orderProduct->getProductEntity());
    }
}
