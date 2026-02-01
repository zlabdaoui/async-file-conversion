<?php

namespace App\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorage
{
    public function store(UploadedFile $file, string $storedFilename): void;
}

