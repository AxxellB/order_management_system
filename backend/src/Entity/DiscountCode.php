<?php

namespace App\Entity;

use App\Repository\DiscountCodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountCodeRepository::class)]
class DiscountCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $couponCode = null;

    #[ORM\Column]
    private ?float $percentOff = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $expirationDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function setCouponCode(string $couponCode): static
    {
        $this->couponCode = $couponCode;

        return $this;
    }

    public function getPercentOff(): ?float
    {
        return $this->percentOff;
    }

    public function setPercentOff(float $percentOff): static
    {
        $this->percentOff = $percentOff;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeImmutable $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
