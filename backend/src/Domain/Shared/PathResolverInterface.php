<?php

namespace App\Domain\Shared;

use App\Domain\File\MediaStorageParams;

interface PathResolverInterface
{
    /**
     * Determines the absolute path where a file is or should be stored
     * within the Storage/Vault directory.
     *
     * When a file name is provided, the returned path points to the file.
     * Otherwise, the returned path points to the target directory.
     *
     * @param string|null $mimeType
     *        MIME type of the file.
     *
     * @param MediaStorageParams $params
     *        Parameters required to build the storage path.
     *
     * @param string|null $fileName
     *        Optional file name. If provided, it is appended to the target directory.
     *
     * @return string Absolute path to the target directory or file.
     */
    public function resolveTargetDirectory(
        ?string $mimeType,
        MediaStorageParams $params,
        ?string $fileName = null
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

    /**
     * Resolve merging  path (or path compenent)
     */
    public function appendPath(string $base, string ...$segments): string;
}