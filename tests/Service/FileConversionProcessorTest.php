<?php

namespace App\Tests\Service;

use App\Exception\MessageSkipException;
use Exception;
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

        $this->fileConversionId = '1dc5cd9a-6bf7-433a-9c03-7d55b0f60e9f';

        $this->fileConversionProcessor = $this->getMockBuilder(FileConversionProcessor::class)
            ->setConstructorArgs([$this->entityManagerMock, $this->fileConversionRepositoryMock, $this->loggerMock])
            ->onlyMethods(['simulateConversion'])
            ->getMock();
    }

    /**
     * @throws MessageSkipException
     */
    public function testProcessSuccess(): void
    {
        $fileConversion = $this->createMock(FileConversion::class);
        $fileConversion->expects($this->once())
            ->method('isProcessable')
            ->willReturn(true);
        $fileConversion->expects($this->once())
            ->method('markAsProcessing');
        $fileConversion->expects($this->once())
            ->method('markAsCompleted');

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->fileConversionId)
            ->willReturn($fileConversion);

        $this->entityManagerMock->expects($this->exactly(2))
            ->method('flush');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                "File conversion with id $this->fileConversionId completed successfully"
            );

        $this->fileConversionProcessor->process($this->fileConversionId);
    }

    /**
     * @throws MessageSkipException
     */
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

    public function testProcessNotProcessable(): void
    {
        $fileConversion = $this->createMock(FileConversion::class);
        $fileConversion->expects($this->once())
            ->method('isProcessable')
            ->willReturn(false);
        $fileConversion->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn(FileConversionStatus::PENDING);

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->fileConversionId)
            ->willReturn($fileConversion);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                "Skipping file conversion with id $this->fileConversionId because it's not processable (status: PENDING)"
            );

        $this->expectException(MessageSkipException::class);
        $this->expectExceptionMessage("File conversion with id $this->fileConversionId is not processable, message will not be retried.");

        $this->fileConversionProcessor->process($this->fileConversionId);
    }

    /**
     * @throws MessageSkipException
     */
    public function testProcessFailure(): void
    {
        $fileConversion = $this->createMock(FileConversion::class);
        $fileConversion->expects($this->once())
            ->method('isProcessable')
            ->willReturn(true);
        $fileConversion->expects($this->once())
            ->method('markAsProcessing');

        $this->fileConversionRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->fileConversionId)
            ->willReturn($fileConversion);

        $this->entityManagerMock->expects($this->exactly(2))
            ->method('flush');

        $this->fileConversionProcessor->expects($this->once())
            ->method('simulateConversion')
            ->will($this->throwException(new Exception('Simulated error')));

        $fileConversion->expects($this->once())
            ->method('markAsFailed');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                "File conversion with id $this->fileConversionId failed"
            );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Error processing file conversion with id: $this->fileConversionId");

        $this->fileConversionProcessor->process($this->fileConversionId);
    }
}
