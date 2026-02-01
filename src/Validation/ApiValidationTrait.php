<?php

namespace App\Validation;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ApiValidationTrait
{
    protected function validateDto(
        object $dto,
        ValidatorInterface $validator
    ): ?JsonResponse {
        $errors = $validator->validate($dto);

        if (count($errors) === 0) {
            return null;
        }

        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }

        return new JsonResponse(['errors' => $messages], 422);
}
}
