<?php

namespace App\Domain\File;

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
        string $ownerId,
        /** @var string[] a array of filename  */
        MediaOwnerType $ownerType,
        MediaPurpose $mediaPurpose,
        ?string $storedFileName = null,
        ?callable  $successCallback = null,
        ?callable  $errorCallback = null,
        string $scope = "public"
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
        string $uniqName, 
        ?string $ownerId = null,
        ?MediaOwnerType $ownerType = null,
        ?MediaPurpose $purpose = null,
        ?callable  $successCallback = null,
        ?callable  $errorCallback = null
    ): void;

    

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @param ?string $accountId                   This is the an uniq id that identify the emplacement where the file will be stored (sub folder identifier)
     * @param ?MediaOwnerType  $ownerType    This describe what type of owner the file belongs to (User, Candidate ..ect). It is used to  create a category folder ...ect
     * @param ?MediaPurpose    $purpose      The purpose indicates the owner sub directory that is follow
     * @return string                       This is the new  file path generated
     */
    public function resolveTargetDirectory(
        ?string $mimeType,
        ?string $ownerId, 
        ?MediaOwnerType $ownerType,
        ?MediaPurpose $purpose
    ): string;

}
