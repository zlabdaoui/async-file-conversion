<?php

namespace App\Tests\Service;

use App\Dto\CreateFileConversionRequest;
use App\Entity\FileConversion;
use App\Service\FileConversionService;
use App\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileConversionServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(FileConversion::class));
        $entityManagerMock->expects($this->once())
            ->method('flush');

        $fileStorageMock = $this->createMock(FileStorage::class);
        $fileStorageMock->expects($this->once())
            ->method('store')
            ->with(
                $this->isInstanceOf(UploadedFile::class),
                $this->callback(fn($filename) => str_ends_with($filename, '.csv'))
            );

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('sample.csv');
        $uploadedFileMock->method('guessExtension')->willReturn('csv');

        $dto = new CreateFileConversionRequest();
        $dto->setFile($uploadedFileMock);
        $dto->setTargetFormat('json');

        $service = new FileConversionService($entityManagerMock, $fileStorageMock);

        $result = $service->create($dto);

        $this->assertSame('sample.csv', $result->getOriginalFilename());
        $this->assertSame('JSON', $result->getTargetFormat());
        $this->assertStringEndsWith('.csv', $result->getStoredFilename());
    }

    public function testStorageFails(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $fileStorageMock = $this->createMock(FileStorage::class);

        $fileStorageMock->method('store')
            ->willThrowException(new FileException('fail'));

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getClientOriginalName')->willReturn('sample.csv');
        $uploadedFileMock->method('guessExtension')->willReturn('csv');

        $dto = new CreateFileConversionRequest();
        $dto->setFile($uploadedFileMock);
        $dto->setTargetFormat('json');

        $service = new FileConversionService($entityManagerMock, $fileStorageMock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to store the uploaded file');

        $service->create($dto);
    }
}
