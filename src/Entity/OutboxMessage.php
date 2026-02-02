<?php

namespace App\Entity;

use App\Repository\OutboxMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OutboxMessageRepository::class)]
class OutboxMessage
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $id;

    #[ORM\Column]
    private array $payload = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dispatchedAt = null;

    public function __construct(array $payload)
    {
        $this->id = Uuid::v4();
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDispatchedAt(): ?\DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function setDispatchedAt(?\DateTimeImmutable $dispatchedAt): static
    {
        $this->dispatchedAt = $dispatchedAt;

        return $this;
    }
}
