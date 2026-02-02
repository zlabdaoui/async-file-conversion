<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\FileConversionProcessor;
use App\Entity\FileConversion;
use App\Entity\FileConversionStatus;
use App\Repository\FileConversionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;

class FileConversionProcessorTest extends TestCase
{
    private MockObject $entityManagerMock;
    private MockObject $fileConversionRepositoryMock;
    private MockObject $loggerMock;
    private FileConversionProcessor $fileConversionProcessor;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->fileConversionRepositoryMock = $this->createMock(FileConversionRepository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->fileConversionProcessor = $this->getMockBuilder(FileConversionProcessor::class)
            ->setConstructorArgs([$this->entityManagerMock, $this->fileConversionRepositoryMock, $this->loggerMock])
            ->onlyMethods(['simulateConversion'])
            ->getMock();
    }

    public function testProcessSuccess(): void
    {
        $fileConversionId = "1dc5cd9a-6bf7-433a-9c03-7d55b0f60e9f";

        $fileConversion = $this->createMock(FileConversion::class);
        $fileConversion->expects($this->once())
            ->method('setStatus')
            ->with(FileConversionStatus::PROCESSING);
        $fileConversion->expects($this->once())
            ->method('setCompletedAt')
            ->with($this->isInstanceOf(\DateTimeImmutable::class));
        $fileConversion->expects($this->once())
            ->method('setStatus')
            ->with(FileConversionStatus::COMPLETED);

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($fileConversionId)
            ->willReturn($fileConversion);

        $this->entityManagerMock->expects($this->exactly(2))
            ->method('flush');

        $this->fileConversionProcessor->process($fileConversionId);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                "File conversion with id $fileConversionId completed successfully"
            );
    }

    public function testProcessFileConversionNotFound(): void
    {
        $fileConversionId = 'fake';

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($fileConversionId)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("File conversion with ID $fileConversionId not found.");

        $this->fileConversionProcessor->process($fileConversionId);
    }

    public function testProcessFailure(): void
    {
        $fileConversionId = '1dc5cd9a-6bf7-433a-9c03-7d55b0f60e9f';

        $fileConversion = $this->createMock(FileConversion::class);
        $fileConversion->expects($this->once())
            ->method('setStatus')
            ->with(FileConversionStatus::PROCESSING);

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($fileConversionId)
            ->willReturn($fileConversion);

        $this->entityManagerMock->expects($this->exactly(2))
            ->method('flush');

        $this->fileConversionProcessor->expects($this->once())
            ->method('simulateConversion')
            ->will($this->throwException(new \Exception('Simulated error')));

        $fileConversion->expects($this->once())
            ->method('setStatus')
            ->with(FileConversionStatus::FAILED);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                "File conversion with id $fileConversionId failed"
            );

        $this->fileConversionProcessor->process($fileConversionId);
    }
}
