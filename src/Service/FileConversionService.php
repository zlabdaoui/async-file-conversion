<?php

namespace App\Service;

use App\Dto\CreateFileConversionRequest;
use App\Entity\FileConversion;
use App\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

readonly class FileConversionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileStorage            $fileStorage
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
        $this->entityManager->flush();

        return $fileConversion;
    }
}
