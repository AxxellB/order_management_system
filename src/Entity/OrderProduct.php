<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
#[ORM\Table(name: 'order_products')]
class OrderProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $pricePerUnit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $subtotal = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderEntity = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $ProductEntity = null;

    public function __construct()
    {
        $this->subtotal = $this->quantity * $this->pricePerUnit;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPricePerUnit(): ?string
    {
        return $this->pricePerUnit;
    }

    public function setPricePerUnit(string $pricePerUnit): static
    {
        $this->pricePerUnit = $pricePerUnit;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getOrderEntity(): ?Order
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(?Order $orderEntity): static
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }

    public function getProductEntity(): ?Product
    {
        return $this->ProductEntity;
    }

    public function setProductEntity(?Product $ProductEntity): static
    {
        $this->ProductEntity = $ProductEntity;

        return $this;
    }

}
