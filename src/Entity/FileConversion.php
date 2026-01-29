<?php

namespace App\Entity;

use App\Repository\FileConversionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileConversionRepository::class)]
class FileConversion
{
    public const string STATUS_PENDING    = 'pending';
    public const string STATUS_PROCESSING = 'processing';
    public const string STATUS_DONE       = 'done';
    public const string STATUS_FAILED     = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 10)]
    private ?string $originalFormat = null;

    #[ORM\Column(length: 10)]
    private ?string $targetFormat = null;

    #[ORM\Column(length: 10)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resultFilename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
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
}
