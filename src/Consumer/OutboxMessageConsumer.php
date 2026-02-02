<?php

namespace App\Consumer;

use App\Entity\OutboxMessage;
use App\Message\FileConversionMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class OutboxMessageConsumer
{

    public function __construct(
        private EntityManagerInterface  $entityManager,
        private MessageBusInterface   $messageBus,
        private LoggerInterface          $logger)
    {
    }

    public function consume(): void
    {
        $failedMessages  = $this->entityManager->getRepository(OutboxMessage::class)
            ->findBy(['dispatchedAt' => null], ['createdAt' => 'ASC']);

        foreach ($failedMessages as $failedMessage) {
            try {
                $message = new FileConversionMessage($failedMessage->getPayload()['conversionId']);
                $this->messageBus->dispatch($message);

                $failedMessage->setDispatchedAt(new \DateTimeImmutable());
                $this->entityManager->flush();

                $this->logger->info("Successfully retried dispatch for file conversion id: " . $failedMessage->getPayload()['conversionId']);
            } catch (ExceptionInterface $e) {
                $this->logger->error("Failed retrying dispatch for file conversion id: " . $failedMessage->getPayload()['conversionId'], [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

