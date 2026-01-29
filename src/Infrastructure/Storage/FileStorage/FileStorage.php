<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\services\FileStorage\FileOwnerType;
use App\Domain\services\FileStorage\FilePurpose;
use App\Domain\services\FileStorage\FileStorageException;
use App\Domain\services\FileStorage\FileStorageInterface;
use App\Domain\Image\UploadedImage;

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

    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param ?array $files                 UploadedFile - Currently the UploadedFile object of symfony
     * @param ?string $id                   Here, you  pass the user/owner Id
     * @param ?FileOwnerType $ownerType     Here you indicate what type of user this recording concern
     * @param ?FilePurpose   $purpose
     * @throws FileStorageException         This exception is throw when recording one of the file failed
     * @return array<UploadedImage>                An empty or filled array
     */
    public function store(array $files, ?string $id, ?FileOwnerType $ownerType, ?FilePurpose $purpose): array
    {
        $filenameCollection = [];
        if(count($files) < 1){
            return $filenameCollection;
        }
            
        $count = 0;
        foreach($files as $file){
            if(!$file instanceof UploadedFile){
                continue;
            }
            $mimeType = $file->getMimeType();
            $targetDir = $this->resolveTargetDirectory($mimeType, $id, $ownerType, $purpose);
            
            if(!is_dir($targetDir)){
                mkdir($targetDir, 0775, true);
            }
            
            $extension = $file->guessClientExtension() ?? $file->getClientOriginalExtension() ?? '';
            $filename = uniqid('', true) . "." . $extension;

            try{
                $file->move($targetDir, $filename);
            }
            catch(FileException $e){
                throw new FileStorageException(
                    'Failed to store' . $file->getClientOriginalName(),
                    previous: $e
                );
            }

            $filenameCollection[] = new UploadedImage($count, $filename, $file->getSize(), $mimeType);
            $count++;
        }

        return $filenameCollection;
    }

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @param ?string $id                   This is the an uniq id that identify the emplacement where the file will be stored (sub folder identifier)
     * @param ?FileOwnerType  $ownerType    This describe what type of owner the file belongs to (User, Candidate ..ect). It is used to  create a category folder ...ect
     * @param ?FilePurpose    $purpose      The purpose indicates the owner sub directory that is follow
     * @return string                       This is the new  file path generated
     */
    private function resolveTargetDirectory(?string $mimeType, ?string $id,  ?FileOwnerType $ownerType, ?FilePurpose $purpose): string
    {
        $base = match(true){
                str_starts_with((string)$mimeType, '/image') => $this->baseStoragePath . $this->relatifPath  . '/images',
                str_starts_with((string)$mimeType, '/video') => $this->baseStoragePath . $this->relatifPath  . '/videos',
                str_starts_with((string)$mimeType, '/audio') => $this->baseStoragePath . $this->relatifPath  . '/audios',
                str_starts_with((string)$mimeType, '/application/pdf') => $this->baseStoragePath . 'documents',
                default => $this->baseStoragePath . '/others'
        };
        if($ownerType){
            $base .= '/' .$ownerType;
        }

        if($id){
            $base .= '/' .  $id;
        }

        if($purpose){
            $base .= '/' . $purpose;
        }

        return $base;
    }
}