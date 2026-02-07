<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\FileOwnerType;
use App\Domain\File\FilePurpose;
use App\Domain\File\FileStorageInterface;
use App\Domain\File\FileUploadResult;
use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FileStorage implements FileStorageInterface
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
    private string $relatifPath = '/Infrastructure/Storage/Vault';

    public function __construct(string $baseStoragePath){
        $this->baseStoragePath = rtrim($baseStoragePath, '/');
    }


    public function store(
        StaticMedia | TimedMedia $file,
        ?string $userId = null,
        ?FileOwnerType $ownerType = null,
        ?FilePurpose $purpose  =null,
        ?int $timeLimitation   = null,
        ?float $sizeLimitation = null,
    ): FileUploadResult
    {
        throw new \Exception('Not implemented');
    }


    public function read(string $name, ?string $id = null, ?FileOwnerType $ownerType = null, ?FilePurpose $purpose = null): void
    {
        throw new \Exception('Not implemented');
    }

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @param ?string $id                   This is the an uniq id that identify the emplacement where the file will be stored (sub folder identifier)
     * @param ?FileOwnerType  $ownerType    This describe what type of owner the file belongs to (User, Candidate ..ect). It is used to  create a category folder ...ect
     * @param ?FilePurpose    $purpose      The purpose indicates the owner sub directory that is follow
     * @return string                       This is the new  file path generated
     */
    private function resolveTargetDirectory(
        ?string $mimeType,
        ?string $id, 
        ?FileOwnerType $ownerType,
        ?FilePurpose $purpose
    ): string
    {

        $base = $this->baseStoragePath . $this->relatifPath ;

        if($ownerType){
            $base .= '/' .$ownerType;
        }

        $path  = match(true){
                str_starts_with((string)$mimeType, '/image') => $base . '/images',
                str_starts_with((string)$mimeType, '/video') => $base . '/videos',
                str_starts_with((string)$mimeType, '/audio') => $base . '/audios',
                str_starts_with((string)$mimeType, '/application/pdf') => $base . 'documents',
                default => $base . '/others'
        };

        if($id){
            $path .= '/' .  $id;
        }

        if($purpose){
            $path .= '/' . $purpose;
        }

        return $path;
    }
}