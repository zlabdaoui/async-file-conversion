<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocalFileStorage implements FileStorage
{
    public function __construct(
        private readonly string $uploadDir
    ) {
        
    }

    public function store(UploadedFile $file, string $storedFilename): void
    {
        try {
            $file->move($this->uploadDir, $storedFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('File upload failed', 0, $e);
        }
    }
}

