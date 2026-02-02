<?php

namespace App\Entity;

use App\Repository\FileConversionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileConversionRepository::class)]
class FileConversion
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename;

    #[ORM\Column(length: 255)]
    private ?string $storedFilename;

    #[ORM\Column(length: 10)]
    private ?string $originalFormat;

    #[ORM\Column(length: 10)]
    private ?string $targetFormat;

    #[ORM\Column(length: 10, enumType: FileConversionStatus::class)]
    private ?FileConversionStatus $status;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resultFilename = null;


    public function __construct(
        Uuid $uuid,
        string $originalFilename,
        string $originalFormat,
        string $targetFormat
    ) {
        $this->id = $uuid;
        $this->originalFilename = $originalFilename;
        $this->originalFormat = $originalFormat;
        $this->targetFormat = $targetFormat;
        $this->status = FileConversionStatus::PENDING;
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

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getOriginalFormat(): ?string
    {
        return $this->originalFormat;
    }

    public function setOriginalFormat(string $originalFormat): static
    {
        $this->originalFormat = $originalFormat;

        return $this;
    }

    public function getTargetFormat(): ?string
    {
        return $this->targetFormat;
    }

    public function setTargetFormat(string $targetFormat): static
    {
        $this->targetFormat = $targetFormat;

        return $this;
    }

    public function getStatus(): ?FileConversionStatus
    {
        return $this->status;
    }

    public function setStatus(FileConversionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getResultFilename(): ?string
    {
        return $this->resultFilename;
    }

    public function setResultFilename(?string $resultFilename): static
    {
        $this->resultFilename = $resultFilename;

        return $this;
    }

    public function markAsProcessing(): static
    {
        $this->status = FileConversionStatus::PROCESSING;
        return $this;
    }

    public function markAsCompleted(): static
    {
        $this->setCompletedAt(new \DateTimeImmutable());
        $this->status = FileConversionStatus::COMPLETED;
        return $this;
    }

    public function markAsFailed(): static
    {
        $this->status = FileConversionStatus::FAILED;
        return $this;
    }

    public function isProcessable(): bool
    {
        return in_array($this->status, [FileConversionStatus::PENDING, FileConversionStatus::FAILED]);
    }
}
