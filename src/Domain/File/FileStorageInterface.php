<?php

namespace App\Domain\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param ?array<UploadedFile>          $files                  UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string                       $id                     Here, you  pass the user/owner Id
     * @param ?FileOwnerType                $ownerType              Here you indicate what type of user this recording concern
     * @param ?FilePurpose                  $purpose                Indicates the purpose of the saving (profile, images...)
     * @param ?FileStorageMode              $mode                   Indicates what type of files in the collection are authorized for saving
     * @return FileUploadResult        An multidimensionsioonal array containing the succed and failed ones
     */
    public function store(
        array $files,
        ?string $id = null,
        ?FileOwnerType $ownerType = null,
        ?FilePurpose $purpose = null,
    ): FileUploadResult;


    public function read(
        string $name, 
        ?string $id = null,
        ?FileOwnerType $ownerType = null,
        ?FilePurpose $purpose = null,
    ): void;
}
