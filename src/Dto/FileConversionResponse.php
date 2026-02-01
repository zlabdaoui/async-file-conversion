<?php

namespace App\Dto;

use App\Entity\FileConversion;

class FileConversionResponse
{
    public string $id;
    public string $originalFilename;
    public string $storedFilename;
    public string $originalFormat;
    public string $targetFormat;
    public string $status;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $completedAt;
    public ?string $resultFilename;

    public static function fromEntity(FileConversion $fileConversion): self
    {
        $dto = new self();
        $dto->id = (string) $fileConversion->getId();
        $dto->originalFilename = $fileConversion->getOriginalFilename();
        $dto->storedFilename = $fileConversion->getStoredFilename();
        $dto->originalFormat = $fileConversion->getOriginalFormat();
        $dto->targetFormat = $fileConversion->getTargetFormat();
        $dto->status = $fileConversion->getStatus()->value;
        $dto->createdAt = $fileConversion->getCreatedAt();
        $dto->completedAt = $fileConversion->getCompletedAt();
        $dto->resultFilename = $fileConversion->getResultFilename();

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'original_filename' => $this->originalFilename,
            'stored_filename' => $this->storedFilename,
            'original_format' => $this->originalFormat,
            'target_format' => $this->targetFormat,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('c'),
            'completed_at' => $this->completedAt?->format('c'),
            'result_filename' => $this->resultFilename,
        ];
    }
}
