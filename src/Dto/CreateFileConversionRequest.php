<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateFileConversionRequest
{
    public const string ERROR_FILE_REQUIRED = 'File is required.';
    public const string ERROR_FILE_EMPTY = 'The uploaded file is empty.';
    public const string ERROR_FILE_INVALID_TYPE = 'Unsupported input file type.';
    public const string ERROR_TARGET_FORMAT_REQUIRED = 'Target format is required.';
    public const string ERROR_TARGET_FORMAT_INVALID = "Target format must be 'json' or 'xml' (case-insensitive).";

    #[Assert\NotNull(message: self::ERROR_FILE_REQUIRED)]
    #[Assert\File(
        mimeTypes: [
            'text/csv',
            'application/json',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet'
        ],
        mimeTypesMessage: self::ERROR_FILE_INVALID_TYPE,
        disallowEmptyMessage: self::ERROR_FILE_EMPTY
    )]
    private ?UploadedFile $file = null;

    #[Assert\NotBlank(message: self::ERROR_TARGET_FORMAT_REQUIRED)]
    #[Assert\Regex(
        pattern: '/^(json|xml)$/i',
        message: self::ERROR_TARGET_FORMAT_INVALID,
    )]
    private ?string $targetFormat = null;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getTargetFormat(): string
    {
        return $this->targetFormat;
    }

    public function setTargetFormat(?string $targetFormat): void
    {
        $this->targetFormat = strtoupper($targetFormat);
    }
}
