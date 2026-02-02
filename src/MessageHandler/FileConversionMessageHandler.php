<?php

namespace App\MessageHandler;

use App\Exception\MessageSkipException;
use App\Message\FileConversionMessage;
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
        try {
            $this->fileConversionProcessor->process($message->getFileConversionId());
        }catch (MessageSkipException){

        }
    }
}
