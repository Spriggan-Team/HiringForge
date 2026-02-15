<?php

namespace App\Domain\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaStorageInterface
{
    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param mixed                                 $files                  UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string                               $ownerId                Here, you  pass the user/owner Id
     * @param string[]                              $skippedFiles           An array containing all the failed or skipped file
     * @param ?MediaOwnerType                       $ownerType              Here you indicate what type of user this recording concern
     * @param ?MediaPurpose                         $mediaPurpose                Indicates the purpose of the saving (profile, images...)
     * @param callable(MediaUploadResult): void     $successCallback
     * @param callable(MediaUploadResult): void     $errorCallback
     * @throws \Exception|App\Domain\Exception\FileExceedTime|\App\Domain\Exception\FileSizeExceeded 
     * @return MediaUploadResult        An multidimensionsioonal array containing the succed and failed ones
     */
    public function store(
        mixed $file,
        ?string $ownerId = null,
        ?string $storedFileName = null,
        /** @var string[] a array of filename  */
        ?MediaOwnerType $ownerType = null,
        ?MediaPurpose $mediaPurpose = null,
        ?callable  $successCallback = null,
        ?callable  $errorCallback = null
    ): void;


    public function read(
        string $name, 
        ?string $id = null,
        ?MediaOwnerType $ownerType = null,
        ?MediaPurpose $purpose = null,
    ): mixed;
}
