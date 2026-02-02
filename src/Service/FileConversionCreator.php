<?php

namespace App\Service;

use App\Dto\CreateFileConversionRequest;
use App\Entity\FileConversion;
use App\Entity\OutboxMessage;
use App\Message\FileConversionMessage;
use App\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class FileConversionCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileStorage            $fileStorage,
        private MessageBusInterface   $messageBus,
        private LoggerInterface          $logger
    ) {}

    public function create(CreateFileConversionRequest $dto): FileConversion
    {
        $fileConversion = new FileConversion(
            $dto->getFile()->getClientOriginalName(),
            $dto->getFile()->guessExtension(),
            $dto->getTargetFormat()
        );

        $storedFilename = $fileConversion->getId() . '.' . $dto->getFile()->guessExtension();

        try {
            $this->fileStorage->store($dto->getFile(), $storedFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to store the uploaded file', 0, $e);
        }

        $fileConversion->setStoredFilename($storedFilename);

        $this->entityManager->persist($fileConversion);

        $outboxMessage = new OutboxMessage(
            ['conversionId' => $fileConversion->getId()]
        );

        $this->entityManager->persist($outboxMessage);

        $this->entityManager->flush();

        try {
            $this->messageBus->dispatch(new FileConversionMessage($fileConversion->getId()));
            $this->logger->info("File conversion with id ". $fileConversion->getId() ." stored and dispatched successfully");
        } catch (ExceptionInterface $e) {
            $this->logger->error("Failed to dispatch FileConversionMessage for file conversion id ".$fileConversion->getId().": " . $e->getMessage());
        }

        return $fileConversion;
    }
}
