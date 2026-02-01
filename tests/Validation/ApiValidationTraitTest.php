<?php

namespace App\Tests\Validation;

use App\Dto\CreateFileConversionRequest;
use App\Validation\ApiValidationTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiValidationTraitTest extends TestCase
{
    use ApiValidationTrait;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testEmptyInputReturnsJsonResponse(): void
    {
        $dto = new CreateFileConversionRequest();

        $response = $this->validateDto($dto, $this->validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('file', $content['errors']);
        $this->assertArrayHasKey('targetFormat', $content['errors']);
        $this->assertSame(
            CreateFileConversionRequest::ERROR_FILE_REQUIRED,
            $content['errors']['file']
        );
        $this->assertSame(
            CreateFileConversionRequest::ERROR_TARGET_FORMAT_REQUIRED,
            $content['errors']['targetFormat']
        );
    }

    public function testInvalidTargetFormatReturnsJsonResponse(): void
    {
        $dto = new CreateFileConversionRequest();

        $dto->setTargetFormat('pdf');

        $response = $this->validateDto($dto, $this->validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('targetFormat', $content['errors']);
        $this->assertSame(
            CreateFileConversionRequest::ERROR_TARGET_FORMAT_INVALID,
            $content['errors']['targetFormat']
        );

    }

    public function testEmptyFileReturnsJsonResponse(): void
    {
        $dto = new CreateFileConversionRequest();

        $dto->setTargetFormat('json');

        $emptyCsvPath = __DIR__ . '/../Fixtures/Files/empty.json';

        $dto->setFile(new UploadedFile(
            $emptyCsvPath,
            'empty.json',
            null,
            null,
            true
        ));

        $response = $this->validateDto($dto, $this->validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('file', $content['errors']);
        $this->assertSame(
            CreateFileConversionRequest::ERROR_FILE_EMPTY,
            $content['errors']['file']
        );
    }

    public function testInvalidFileReturnsJsonResponse(): void
    {
        $dto = new CreateFileConversionRequest();

        $dto->setTargetFormat('json');

        $xmlPath = __DIR__ . '/../Fixtures/Files/sample.xml';

        $dto->setFile(new UploadedFile(
            $xmlPath,
            'sample.xml',
            null,
            null,
            true
        ));

        $response = $this->validateDto($dto, $this->validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('file', $content['errors']);
        $this->assertSame(
            CreateFileConversionRequest::ERROR_FILE_INVALID_TYPE,
            $content['errors']['file']
        );
    }

    public function testValidDtoReturnsNull(): void
    {
        $dto = new CreateFileConversionRequest();
        $dto->setTargetFormat('json');

        $csvPath = __DIR__ . '/../Fixtures/Files/sample.csv';

        $this->assertFileExists($csvPath);

        $dto->setFile(new UploadedFile(
            $csvPath,
            'sample.csv',
            null,
            null,
            true
        ));

        $response = $this->validateDto($dto, $this->validator);

        $this->assertNull($response);
    }

}
