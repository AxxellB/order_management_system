<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Entity\User;
use App\Entity\Order;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testInitialValues(): void
    {
        $address = new Address();

        $this->assertNull($address->getId());
        $this->assertNull($address->getLine());
        $this->assertNull($address->getLine2());
        $this->assertNull($address->getCity());
        $this->assertNull($address->getCountry());
        $this->assertNull($address->getPostcode());
        $this->assertNull($address->getUser());
        $this->assertNull($address->getOrderEntity());
    }

    public function testSetAndGetLine(): void
    {
        $address = new Address();
        $address->setLine('123 Main St');

        $this->assertEquals('123 Main St', $address->getLine());
    }

    public function testSetAndGetLine2(): void
    {
        $address = new Address();
        $address->setLine2('Apt 4B');

        $this->assertEquals('Apt 4B', $address->getLine2());
    }

    public function testSetAndGetCity(): void
    {
        $address = new Address();
        $address->setCity('Springfield');

        $this->assertEquals('Springfield', $address->getCity());
    }

    public function testSetAndGetCountry(): void
    {
        $address = new Address();
        $address->setCountry('USA');

        $this->assertEquals('USA', $address->getCountry());
    }

    public function testSetAndGetPostcode(): void
    {
        $address = new Address();
        $address->setPostcode('12345');

        $this->assertEquals('12345', $address->getPostcode());
    }

    public function testSetAndGetUser(): void
    {
        $address = new Address();
        $user = new User();

        $address->setUser($user);
        $this->assertSame($user, $address->getUser());
    }

    public function testSetAndGetOrderEntity(): void
    {
        $address = new Address();
        $order = new Order();

        $address->setOrderEntity($order);
        $this->assertSame($order, $address->getOrderEntity());
    }

    public function testToString(): void
    {
        $address = new Address();
        $address->setLine('123 Main St')
            ->setCity('Springfield')
            ->setPostcode('12345')
            ->setCountry('USA');

        $expectedString = '123 Main St, Springfield, 12345, USA';
        $this->assertEquals($expectedString, $address->__toString());
    }
}
