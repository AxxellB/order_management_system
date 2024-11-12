<?php

namespace App\Tests\Entity;

use App\Entity\OrderHistoryLogs;
use App\Entity\Order;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class OrderHistoryLogsTest extends TestCase
{
    public function testInitialValues(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();

        $this->assertNull($orderHistoryLog->getId());
        $this->assertNull($orderHistoryLog->getRelatedOrder());
        $this->assertNull($orderHistoryLog->getUser());
        $this->assertNull($orderHistoryLog->getChangeType());
        $this->assertEquals([], $orderHistoryLog->getOldValue());
        $this->assertEquals([], $orderHistoryLog->getNewValue());
        $this->assertNull($orderHistoryLog->getChangedAt());
    }

    public function testSetAndGetRelatedOrder(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $order = new Order();

        $orderHistoryLog->setRelatedOrder($order);
        $this->assertSame($order, $orderHistoryLog->getRelatedOrder());
    }

    public function testSetAndGetUser(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $user = new User();

        $orderHistoryLog->setUser($user);
        $this->assertSame($user, $orderHistoryLog->getUser());
    }

    public function testSetAndGetChangeType(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $orderHistoryLog->setChangeType('Status Update');

        $this->assertEquals('Status Update', $orderHistoryLog->getChangeType());
    }

    public function testSetAndGetOldValue(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $oldValue = ['status' => 'Pending'];
        $orderHistoryLog->setOldValue($oldValue);

        $this->assertEquals($oldValue, $orderHistoryLog->getOldValue());
    }

    public function testSetAndGetNewValue(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $newValue = ['status' => 'Completed'];
        $orderHistoryLog->setNewValue($newValue);

        $this->assertEquals($newValue, $orderHistoryLog->getNewValue());
    }

    public function testSetAndGetChangedAt(): void
    {
        $orderHistoryLog = new OrderHistoryLogs();
        $changedAt = new \DateTimeImmutable('2023-12-31 12:00:00');
        $orderHistoryLog->setChangedAt($changedAt);

        $this->assertEquals($changedAt, $orderHistoryLog->getChangedAt());
    }
}
