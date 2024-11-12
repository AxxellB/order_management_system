<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\OrderHistoryLogs;
use App\Entity\User;
use App\Entity\Address;
use App\Enum\OrderStatus;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

class OrderTest extends TestCase
{
    public function testOrderCreation(): void
    {
        $order = new Order();
        $this->assertInstanceOf(Order::class, $order);
    }

    public function testSetAndGetOrderDate(): void
    {
        $order = new Order();
        $orderDate = new \DateTime();

        $order->setOrderDate($orderDate);

        $this->assertSame($orderDate, $order->getOrderDate());
    }

    public function testSetAndGetTotalAmount(): void
    {
        $order = new Order();
        $totalAmount = '150.14';

        $order->setTotalAmount($totalAmount);

        $this->assertSame($totalAmount, $order->getTotalAmount());
    }

    public function testSetAndGetPaymentMethod(): void
    {
        $order = new Order();
        $paymentMethod = 'Revolut';

        $order->setPaymentMethod($paymentMethod);

        $this->assertSame($paymentMethod, $order->getPaymentMethod());
    }

    public function testSetAndGetStatus(): void
    {
        $order = new Order();
        $status = OrderStatus::PROCESSING;

        $order->setStatus($status);

        $this->assertSame($status, $order->getStatus());
    }

    public function testSetAndGetUser(): void
    {
        $order = new Order();
        $user = new User();

        $order->setUserId($user);

        $this->assertSame($user, $order->getUserId());
    }

    public function testSetAndGetDeletedAt(): void
    {
        $order = new Order();
        $deletedAt = new \DateTimeImmutable();

        $order->setDeletedAt($deletedAt);

        $this->assertSame($deletedAt, $order->getDeletedAt());
    }

    public function testAddAndRemoveOrderProduct(): void
    {
        $order = new Order();
        $orderProduct = $this->createMock(OrderProduct::class);

        $order->addOrderProduct($orderProduct);
        $this->assertCount(1, $order->getOrderProducts());
        $this->assertSame($orderProduct, $order->getOrderProducts()->first());

        $order->removeOrderProduct($orderProduct);
        $this->assertCount(0, $order->getOrderProducts());
    }

    public function testSetAndGetAddress(): void
    {
        $order = new Order();
        $address = new Address();

        $order->setAddress($address);

        $this->assertSame($address, $order->getAddress());

        $order->setAddress(null);
        $this->assertNull($order->getAddress());
    }

    public function testAddAndRemoveOrderHistoryLog(): void
    {
        $order = new Order();
        $orderHistoryLog = $this->createMock(OrderHistoryLogs::class);

        $order->addOrderHistoryLog($orderHistoryLog);
        $this->assertCount(1, $order->getOrderHistoryLogs());
        $this->assertSame($orderHistoryLog, $order->getOrderHistoryLogs()->first());

        $order->removeOrderHistoryLog($orderHistoryLog);
        $this->assertCount(0, $order->getOrderHistoryLogs());
    }

    public function testSetAndGetDiscountCode(): void
    {
        $order = new Order();
        $discountCode = 'SAVE20';

        $order->setDiscountCode($discountCode);

        $this->assertSame($discountCode, $order->getDiscountCode());
    }

    public function testSetAndGetDiscountPercentOff(): void
    {
        $order = new Order();
        $discountPercentOff = 15.5;

        $order->setDiscountPercentOff($discountPercentOff);

        $this->assertSame($discountPercentOff, $order->getDiscountPercentOff());
    }

    public function testOrderHistoryLogsInitialization(): void
    {
        $order = new Order();
        $this->assertInstanceOf(Collection::class, $order->getOrderHistoryLogs());
        $this->assertCount(0, $order->getOrderHistoryLogs());
    }

    public function testOrderProductsInitialization(): void
    {
        $order = new Order();
        $this->assertInstanceOf(Collection::class, $order->getOrderProducts());
        $this->assertCount(0, $order->getOrderProducts());
    }
}
