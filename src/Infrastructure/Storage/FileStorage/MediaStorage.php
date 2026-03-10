<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\File\MediaUploadResult;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class MediaStorage implements MediaStorageInterface
{
    /**
     * This is the project dir to the root 'src' folder
     * @property string
     */
    private string $baseStoragePath;

    /**
     * This is the relative path from the src folder ("/path")
     * @var string $relatifPath
     */
    private string $relatifPath = '/Vault';

    public function __construct(
        private Filesystem $filesystem,
        string $baseStoragePath
    ){
        $this->baseStoragePath = rtrim($baseStoragePath, '/');
    }


    public function store(
        /** @var  UploadedFile */
        mixed $file,
        ?string $ownerId = null,
        ?string $storedFileName = null,
        /** @var string[] a array of filename  */
        ?MediaOwnerType $ownerType = null,
        ?MediaPurpose $mediaPurpose = null,
        ?callable  $successCallback = null,
        ?callable  $errorCallback = null
    ): void
    {
        if(!$file instanceof UploadedFile)
        {
            throw new \Exception("[MediaStorage::strore] Such a class of file is not supported yet!!");
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname());
        $path = $this->resolveTargetDirectory(
            $mime, $ownerId, $ownerType, $mediaPurpose
        );

        if(!$this->filesystem->exists($path)){
            $this->filesystem->mkdir($path, 0775);
        }

        $extension = $file->guessExtension() ?: 'bin';
        $fileName = $storedFileName ?? (Uuid::v4()->toRfc4122() . '.' . $extension);

        $uploadResult = new MediaUploadResult(
                originalName: $file->getClientOriginalName() ?? "unknow",
                storedName: $fileName,
        );
        
        try{
            $file->move($path, $fileName);

            if($successCallback){
                $successCallback($uploadResult);
            }
        }
        catch(FileException $exception)
        {
            if($errorCallback){
                $errorCallback($uploadResult);
            }
        }
    }


    public function read(string $name, ?string $id = null, ?MediaOwnerType $ownerType = null, ?MediaPurpose $purpose = null): mixed
    {
        throw new \Exception('Not implemented');
    }

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @param ?string $id                   This is the an uniq id that identify the emplacement where the file will be stored (sub folder identifier)
     * @param ?MediaOwnerType  $ownerType    This describe what type of owner the file belongs to (User, Candidate ..ect). It is used to  create a category folder ...ect
     * @param ?MediaPurpose    $purpose      The purpose indicates the owner sub directory that is follow
     * @return string                       This is the new  file path generated
     */
    private function resolveTargetDirectory(
        ?string $mimeType,
        ?string $id, 
        ?MediaOwnerType $ownerType,
        ?MediaPurpose $purpose
    ): string
    {

        $base = $this->baseStoragePath . $this->relatifPath ;

        if($ownerType){
            $base .= '/' .$ownerType->value;
        }

        $path  = match(true){
                str_starts_with((string)$mimeType, 'image') => $base . '/images',
                str_starts_with((string)$mimeType, 'video') => $base . '/videos',
                str_starts_with((string)$mimeType, 'audio') => $base . '/audios',
                str_starts_with((string)$mimeType, 'application/pdf') => $base . '/documents',
                default => $base . '/others'
        };

        if($id){
            $path .= '/' .  $id;
        }

        if($purpose){
            $path .= '/' . $purpose->value;
        }

        return $path;
    }



    public function remove(string $uniqName, ?string $ownerId = null, ?MediaOwnerType $ownerType = null, ?MediaPurpose $purpose = null): void
    {
        throw new \Exception('Not implemented');
    }
}