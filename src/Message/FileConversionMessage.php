<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final readonly class FileConversionMessage
{
    public function __construct(
        public string $fileConversionId
    ) {

    }

    public function getFileConversionId(): string
    {
        return $this->fileConversionId;
    }
}
