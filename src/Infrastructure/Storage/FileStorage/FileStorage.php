<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\services\FileStorage\FileOwnerType;
use App\Domain\services\FileStorage\FilePurpose;
use App\Domain\services\FileStorage\FileStorageInterface;
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
     * This is the relative path from the src folder
     * @property string
     */
    private string $relatifPath = '/Infrastructure/Storage/Vault';

    public function __construct(string $baseStoragePath){
        $this->baseStoragePath = rtrim($baseStoragePath, '/');
    }

    /**
     * This function stored an array of files into the followwing repertory: Infrastructure/Storage/vault
     * 
     * @param files[]       UploadedFile - Currently the UploadedFile object of symfony
     * @param id            Here, you must pass the user/owner Id
     * @param 
     * @return array        An empty or filled array
     */
    public function store(array $files, ?string $id, ?FileOwnerType $ownerType, ?FilePurpose $purpose): array
    {
        $filenameCollection = [];
        if(count($files) < 1){
            return $filenameCollection;
        }
            
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

            $filenameCollection[] = $filename;
        }

        return $filenameCollection;
    }

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param mimeType this is the mime type of the file that is to be recorded
     * @param id       This is the an uniq id that identify the emplacement where the file will be stored (sub folder identifier)
     * @param category This describe what type of owner the file belongs to (User, Candidate ..ect). It is used to  create a category folder ...ect
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