<?php

namespace App\Controller;

use App\Dto\CreateFileConversionRequest;
use App\Dto\FileConversionResponse;
use App\Entity\FileConversion;
use App\Service\FileConversionService;
use App\Validation\ApiValidationTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/file-conversions")]
class FileConversionsController extends AbstractController
{
    use ApiValidationTrait;

    #[Route("", name: "create_file_conversion", methods: ["POST"])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        FileConversionService $fileConversionService): JsonResponse
    {
        $dto = new CreateFileConversionRequest();
        $dto->setFile($request->files->get('file'));
        $dto->setTargetFormat($request->request->get('target_format'));

        if ($response = $this->validateDto($dto, $validator)) {
            return $response;
        }

        try {
            $fileConversion = $fileConversionService->create($dto);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(
            FileConversionResponse::fromEntity($fileConversion)->toArray(),
            202
        );
    }

    #[Route("/{id}", name: "get_file_conversion")]
    public function getById(
        string $id,
        EntityManagerInterface $em): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return $this->json(['error' => 'Invalid ID format'], 400);
        }

        $fileConversion = $em->getRepository(FileConversion::class)->find($id);

        if (!$fileConversion) {
            return $this->json(['error' => 'File conversion not found'], 404);
        }

        return new JsonResponse(
            FileConversionResponse::fromEntity($fileConversion)->toArray(),
            200
        );
    }
}
