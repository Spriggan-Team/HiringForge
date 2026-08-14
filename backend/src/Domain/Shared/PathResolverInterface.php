<?php

namespace App\Domain\Shared;

use App\Domain\File\MediaStorageParams;

interface PathResolverInterface
{
    /**
     * This function is used to determinate where the file is or should precisily be  stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @param  MediaStorageParams  $params  contains all necessary param for building path
     * @return string                       This is the new  file path generated
     */
    public function resolveTargetDirectory(
        ?string $mimeType,
        MediaStorageParams $params,
    ): string;


    /** * Resolve an absolute path according to the requested root. 
     *  
     *  SYSTEM → absolute filesystem path 
     *  PROJECT → path relative to project root 
     *  PUBLIC → path relative to public directory 
    */
    public function resolvePath(
        string $absolutePath,
        RootPath $rootPath
    ): string;
}