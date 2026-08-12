<?php

namespace App\Domain\File;

use App\Domain\Shared\RootPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaStorageInterface
{
    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param mixed                                 $files                  UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string                               $ownerId                Here, you  pass the user/owner Id - determines what main folder should be used for this actions 
     * @param ?string                                $storedFileName         An array containing all the failed or skipped file 
     * @param ?string                               $ownerType              Optionnal - Describe a name you want to give to the file to record, 
     *                                                                                  if not provided an uniq name will be design the the file (using an algorithm...)
     * 
     * @param ?MediaPurpose                         $mediaPurpose                Indicates the purpose of the saving (profile, images...)
     * @param callable(MediaUploadResult): void     $successCallback
     * @param callable(MediaUploadResult): void     $errorCallback
     * 
     * @throws \Exception
     * 
     * @return void        An multidimensionsioonal array containing the succed and failed ones
     */
    public function store(
        mixed $file,
        /** @var string[] a array of filename  */
        MediaStorageParams $params,
        ?string $storedFileName = null,
        ?callable $successCallback = null, 
        ?callable $errorCallback = null,
    ): void;


    /**
     * Used for reding puprose
     */
    public function read(
        string $name, 
        ?string $ownerId = null,
        ?MediaOwnerType $ownerType = null,
        ?MediaPurpose $purpose = null,
    ): mixed;


    /**
     * Used for removing
     * @throws \Exception
     */
    public function remove(
        MediaStorageParams $params,
        ?string $mimeType = null,
        bool $recursive = false,
        ?callable $successCallback = null, 
        ?callable $errorCallback = null,
    ): void;

    

}
