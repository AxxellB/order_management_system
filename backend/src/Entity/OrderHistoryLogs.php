<?php

namespace App\Entity;

use App\Repository\OrderHistoryLogsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderHistoryLogsRepository::class)]
#[ORM\Table(name: 'order_history_logs')]
class OrderHistoryLogs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderHistoryLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $relatedOrder = null;

    #[ORM\ManyToOne(inversedBy: 'orderHistoryLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $changeType = null;

    #[ORM\Column]
    private array $oldValue = [];

    #[ORM\Column]
    private array $newValue = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $changedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    public function setRelatedOrder(?Order $relatedOrder): static
    {
        $this->relatedOrder = $relatedOrder;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getChangeType(): ?string
    {
        return $this->changeType;
    }

    public function setChangeType(string $changeType): static
    {
        $this->changeType = $changeType;

        return $this;
    }

    public function getOldValue(): array
    {
        return $this->oldValue;
    }

    public function setOldValue(array $oldValue): static
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): array
    {
        return $this->newValue;
    }

    public function setNewValue(array $newValue): static
    {
        $this->newValue = $newValue;

        return $this;
    }

    public function getChangedAt(): ?\DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function setChangedAt(\DateTimeImmutable $changedAt): static
    {
        $this->changedAt = $changedAt;

        return $this;
    }
}
