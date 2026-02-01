<?php

namespace App\Tests\Controller;

use App\Entity\FileConversion;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class FileConversionsControllerTest extends WebTestCase
{

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $uploadDir = self::getContainer()->getParameter('upload_directory');

        if (is_dir($uploadDir)) {
            array_map('unlink', glob($uploadDir . '/*'));
        }

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\FileConversion')->execute();
    }

    public function testCreateFileConversionHappyPath(): void
    {
        $csvContent = <<<CSV
                            name,age,city
                            Alice,30,London
                            Bob,25,Paris
                       CSV;

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_csv_');
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'sample.csv',
            'text/csv',
            null,
            true
        );


        $this->client->request(
            'POST',
            '/file-conversions',
            ['target_format' => 'json'],
            ['file' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(202);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        $em = self::getContainer()->get('doctrine')->getManager();
        $entity = $em->getRepository(FileConversion::class)->find($data['id']);

        $this->assertNotNull($entity);
        $this->assertSame('JSON', $entity->getTargetFormat());
        $this->assertSame('sample.csv', $entity->getOriginalFilename());

        $this->client->request(
            'GET',
            '/file-conversions/' . $data['id']
        );

        $this->assertResponseStatusCodeSame(200);

    }

    public function testCreateFileConversionFailsWithUnsupportedFileType(): void
    {
        $xmlPath = __DIR__ . '/../Fixtures/Files/sample.xml';

        $uploadedFile = new UploadedFile(
            $xmlPath,
            'sample.xml',
            'application/xml',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/file-conversions',
            ['target_format' => 'json'],
            ['file' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateFileConversionFailsWithInvalidTargetFormat(): void
    {
        $csvContent = <<<CSV
                            name,age,city
                            Alice,30,London
                            Bob,25,Paris
                       CSV;

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_csv_');
        file_put_contents($tmpFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'sample.csv',
            'text/csv',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/file-conversions',
            ['target_format' => 'pdf'],
            ['file' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetFileConversionNotFound(): void
    {
        $this->client->request(
            'GET',
            '/file-conversions/' . Uuid::v4()->toRfc4122()
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
