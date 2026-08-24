<?php

namespace App\Domain\File;


interface MediaStorageInterface
{
    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param mixed                                  $file                  UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string                                $storedFileName         An array containing all the failed or skipped file 
     *                                                                                  if not provided an uniq name will be design the the file (using an algorithm...)
     * 
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
        ?string $fileName = null,
        ?string $mimeType = null,
        bool $recursive = false,
        ?callable $successCallback = null, 
        ?callable $errorCallback = null,
    ): void;


    /**
     * Stores a file temporarily.
     *
     * @param mixed       $file            File to store temporarily.
     * @param string|null $storedFileName  Optional name for the stored file.
     *                                     If not provided, a unique name is generated.
     *
     * @throws \InvalidArgumentException If the file cannot be stored.
     *
     * @return string The path to the temporary file.
     */
    public function storeTemp(mixed $file, ?string $storedFileName =null): string;


    /**
     * Deletes a temporary file.
     */
    public function deleteTemp(string $path): void;

    /**
     * Moves a temporary file to its final storage location.
     * @throws \Exception
     */
    public function moveToFinal(string $tempPath, MediaStorageParams $params): void;
}
