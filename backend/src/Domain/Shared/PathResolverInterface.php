<?php

namespace App\Domain\Shared;

use App\Domain\File\MediaStorageParams;

interface PathResolverInterface
{
    /**
     * Resolves the absolute path where a file is or should be stored
     * within the Storage/Vault directory.
     *
     * When a file name is provided, the returned path points to the file.
     * Otherwise, the returned path points to the target directory.
     *
     * This method provides a general-purpose path resolution mechanism,
     * while resolveFilePath() and resolveDirectoryPath() provide stricter
     * guarantees depending on the expected path type.
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
    public function resolveStoragePath(
        ?string $mimeType,
        MediaStorageParams $params,
        ?string $fileName = null
    ): string;


    /**
     * Resolves a file path and ensures that it references either
     * an existing file or a valid path that can be used as a file destination.
     *
     * This method strictly resolves paths intended to reference a file.
     *
     * @param string|null $mimeType
     *        MIME type of the file.
     *
     * @param MediaStorageParams $params
     *        Parameters required to build the storage path.
     *
     * @param string|null $fileName
     *        Optional file name used to resolve the file path.
     *
     * @return string Absolute path to the file or a valid file destination.
     */
    public function resolveFilePath(
        ?string $mimeType,
        MediaStorageParams $params,
        ?string $fileName = null
    ): string;



    /**
     * Resolves a directory path and ensures that it is a valid path
     * within the project's storage structure.
     *
     * The returned path may reference an existing directory or a valid
     * location that can be used as a destination for a directory.
     *
     * This method strictly resolves paths intended to reference a directory.
     *
     * @param string|null $mimeType
     *        MIME type used to determine the storage directory.
     *
     * @param MediaStorageParams $params
     *        Parameters required to build the storage path.
     *
     * @param string|null $fileName
     *        Optional file name used when resolving the target location.
     *
     * @return string Absolute path to the directory or a valid directory destination.
     */
    public function resolveDirectoryPath(
        ?string $mimeType,
        MediaStorageParams $params,
    ):string;



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

    
    /**
     * Return MIME type guessed by file extension.
     */
    public function resolveMimeType(string $fileName): string;
}