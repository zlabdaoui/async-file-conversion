<?php

namespace App\Service;

use App\Entity\FileConversion;
use App\Entity\FileConversionStatus;
use App\Repository\FileConversionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\RuntimeException;

readonly class FileConversionProcessor
{

    public function __construct(
        private EntityManagerInterface   $entityManager,
        private FileConversionRepository $fileConversionRepository,
        private LoggerInterface          $logger
    )
    {
    }

    public function process(string $fileConversionId): void
    {
        $fileConversion = $this->fileConversionRepository->find($fileConversionId);
        if (!$fileConversion) {
            $this->logger->warning("File conversion not found for id: $fileConversionId");
            throw new RunTimeException("File conversion with ID $fileConversionId not found.");
        }

        $fileConversion->setStatus(FileConversionStatus::PROCESSING);
        $this->entityManager->flush();

        try {
            $this->simulateConversion($fileConversion);

            $fileConversion->setCompletedAt(new \DateTimeImmutable());
            $fileConversion->setStatus(FileConversionStatus::COMPLETED);
            $this->entityManager->flush();

            $this->logger->info("File conversion with id $fileConversionId completed successfully", ["conversionId" => $fileConversionId]);
        } catch (\Exception $e) {
            $fileConversion->setStatus(FileConversionStatus::FAILED);
            $this->entityManager->flush();

            $this->logger->error("File conversion with id $fileConversionId failed", [
                "fileConversionId" => $fileConversionId,
                "error" => $e->getMessage()
            ]);

            throw new RuntimeException("Error processing file conversion with id: $fileConversionId", 0, $e);
        }
    }

    protected function simulateConversion(FileConversion $fileConversion): void
    {
        // Simulate the conversion process (dummy logic for now)
        sleep(3);
        $fileConversion->setResultFilename('converted_'.$fileConversion->getId() . '.' . $fileConversion->getTargetFormat());
    }
}
