<?php

namespace App\Domain\Services\FileStorage;

use App\Domain\services\FileStorage\FileOwnerType;
use App\Domain\services\FileStorage\FilePurpose;
use Symfony\Component\HttpFoundation\File\UploadedFile;


interface FileStorageInterface
{
    /**
     * Stores the given files in the Storage vault.
     *
     * @param UploadedFile[] $files Array of uploaded files
     * @return string[] Array of stored filenames (without extension). This array length can be 0
     * @throws NFSWException inappropriate image uploaded
     */
    public function store(array $files, ?string $id, ?FileOwnerType $ownerType, ?FilePurpose $purpose): array;
}
