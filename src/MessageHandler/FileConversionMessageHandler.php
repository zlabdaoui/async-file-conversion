<?php

namespace App\MessageHandler;

use App\Message\FileConversionMessage;
use App\Service\FileConversionCreator;
use App\Service\FileConversionProcessor;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FileConversionMessageHandler
{
    private FileConversionProcessor $fileConversionProcessor;

    public function __construct(FileConversionProcessor $fileConversionProcessor)
    {
        $this->fileConversionProcessor = $fileConversionProcessor;
    }
    public function __invoke(FileConversionMessage $message): void
    {
        $this->fileConversionProcessor->process($message->getFileConversionId());
    }
}
