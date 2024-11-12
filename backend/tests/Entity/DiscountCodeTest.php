<?php

namespace App\Tests\Entity;

use App\Entity\DiscountCode;
use PHPUnit\Framework\TestCase;

class DiscountCodeTest extends TestCase
{
    public function testInitialValues(): void
    {
        $discountCode = new DiscountCode();

        $this->assertNull($discountCode->getId());
        $this->assertNull($discountCode->getCouponCode());
        $this->assertNull($discountCode->getPercentOff());
        $this->assertNull($discountCode->getExpirationDate());
    }

    public function testSetAndGetCouponCode(): void
    {
        $discountCode = new DiscountCode();
        $discountCode->setCouponCode('SAVE20');

        $this->assertEquals('SAVE20', $discountCode->getCouponCode());
    }

    public function testSetAndGetPercentOff(): void
    {
        $discountCode = new DiscountCode();
        $discountCode->setPercentOff(20.0);

        $this->assertEquals(20.0, $discountCode->getPercentOff());
    }

    public function testSetAndGetExpirationDate(): void
    {
        $discountCode = new DiscountCode();
        $expirationDate = new \DateTimeImmutable('2023-12-31');
        $discountCode->setExpirationDate($expirationDate);

        $this->assertEquals($expirationDate, $discountCode->getExpirationDate());
    }

    public function testExpirationDateIsImmutable(): void
    {
        $discountCode = new DiscountCode();
        $expirationDate = new \DateTimeImmutable('2023-12-31');
        $discountCode->setExpirationDate($expirationDate);

        $newDate = $expirationDate->modify('+1 day');
        $this->assertNotEquals($newDate, $discountCode->getExpirationDate());
        $this->assertEquals($expirationDate, $discountCode->getExpirationDate());
    }
}
