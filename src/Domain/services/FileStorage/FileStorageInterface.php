<?php

namespace App\Domain\Services\FileStorage;

use App\Domain\Services\FileStorage\FileOwnerType;
use App\Domain\Services\FileStorage\FilePurpose;
use App\Domain\Image\UploadedImage;

interface FileStorageInterface
{
    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param ?array $files                 UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string $id                   Here, you  pass the user/owner Id
     * @param ?FileOwnerType $ownerType     Here you indicate what type of user this recording concern
     * @param ?FilePurpose   $purpose
     * @throws FileStorageException         This exception is thrown when recording one of the file failed
     * @return array<UploadedImage>         An empty or filled array
     */
    public function store(array $files, ?string $id, ?FileOwnerType $ownerType, ?FilePurpose $purpose): array;
}
