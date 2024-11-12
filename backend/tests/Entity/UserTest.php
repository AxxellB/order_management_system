<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderHistoryLogs;
use App\Entity\Basket;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        $user->setFirstName('John');
        $this->assertSame('John', $user->getFirstName());

        $user->setLastName('Doe');
        $this->assertSame('Doe', $user->getLastName());

        $user->setEmail('john.doe@example.com');
        $this->assertSame('john.doe@example.com', $user->getEmail());

        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testRoles()
    {
        $user = new User();

        $this->assertSame(['ROLE_USER'], $user->getRoles());
        
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());

        $this->assertTrue($user->isAdmin());
    }

    public function testAddresses()
    {
        $user = new User();
        $address = new Address();

        $user->addAddress($address);
        $this->assertCount(1, $user->getAddresses());
        $this->assertSame($user, $address->getUser());

        $address->setId(1);
        $this->assertSame($address, $user->getAddress(1));

        $user->removeAddress($address);
        $this->assertCount(0, $user->getAddresses());
        $this->assertNull($address->getUser());
    }

    public function testOrders()
    {
        $user = new User();
        $order = new Order();

        $user->addOrder($order);
        $this->assertCount(1, $user->getOrders());
        $this->assertSame($user, $order->getUserId());

        $user->removeOrder($order);
        $this->assertCount(0, $user->getOrders());
        $this->assertNull($order->getUserId());
    }

    public function testOrderHistoryLogs()
    {
        $user = new User();
        $log = new OrderHistoryLogs();

        $user->addOrderHistoryLog($log);
        $this->assertCount(1, $user->getOrderHistoryLogs());
        $this->assertSame($user, $log->getUser());

        $user->removeOrderHistoryLog($log);
        $this->assertCount(0, $user->getOrderHistoryLogs());
        $this->assertNull($log->getUser());
    }

    public function testBasket()
    {
        $user = new User();
        $basket = new Basket();

        $user->setBasket($basket);
        $this->assertSame($basket, $user->getBasket());
        $this->assertSame($user, $basket->getUser());
    }

    public function testDeletedAt()
    {
        $user = new User();
        $deletedAt = new \DateTimeImmutable('now');

        $user->setDeletedAt($deletedAt);
        $this->assertSame($deletedAt, $user->getDeletedAt());
    }

    public function testUserIdentifier()
    {
        $user = new User();
        $user->setEmail('john.doe@example.com');
        $this->assertSame('john.doe@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials()
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true, 'eraseCredentials does not alter any properties directly in this test.');
    }
}
