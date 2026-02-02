<?php

namespace App\Tests\Service;

use App\Dto\CreateFileConversionRequest;
use App\Entity\FileConversion;
use App\Message\FileConversionMessage;
use App\Service\FileConversionCreator;
use App\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class FileConversionCreatorTest extends TestCase
{

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->fileStorageMock = $this->createMock(FileStorage::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->fileConversionCreator = new FileConversionCreator(
            $this->entityManagerMock,
            $this->fileStorageMock,
            $this->messageBusMock,
            $this->loggerMock
        );
    }

    public function testCreateSuccess(): void
    {
        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(FileConversion::class));
        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->fileStorageMock->expects($this->once())
            ->method('store')
            ->with(
                $this->isInstanceOf(UploadedFile::class),
                $this->callback(fn($filename) => str_ends_with($filename, '.csv'))
            );
        $this->messageBusMock->method('dispatch')
            ->with($this->isInstanceOf(FileConversionMessage::class));

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('sample.csv');
        $uploadedFileMock->method('guessExtension')->willReturn('csv');

        $dto = new CreateFileConversionRequest();
        $dto->setFile($uploadedFileMock);
        $dto->setTargetFormat('json');

        $result = $this->fileConversionCreator->create($dto);

        $this->assertSame('sample.csv', $result->getOriginalFilename());
        $this->assertSame('JSON', $result->getTargetFormat());
        $this->assertStringEndsWith('.csv', $result->getStoredFilename());
    }

    public function testStorageFails(): void
    {
        $this->fileStorageMock = $this->createMock(FileStorage::class);

        $this->fileStorageMock->method('store')
            ->willThrowException(new FileException('fail'));

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('sample.csv');
        $uploadedFileMock->method('guessExtension')->willReturn('csv');

        $dto = new CreateFileConversionRequest();
        $dto->setFile($uploadedFileMock);
        $dto->setTargetFormat('json');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to store the uploaded file');

        $this->fileConversionCreator->create($dto);
    }
}
