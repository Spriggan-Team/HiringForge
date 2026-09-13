<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\Shared\RootPath;
use App\Domain\File\MediaStorageParams;
use App\Domain\Shared\PathResolverInterface;
use Override;

class LocalPathResolver implements PathResolverInterface
{
    private string $projectDir;

    public function __construct(
        string $projectDir
    ){
        $this->projectDir = $projectDir;
    }

    /**
     * This function is used to determinate where the file should precisily be stored in the 'Storage/Vault' folder
     * @param ?string $mimeType             this is the mime type of the file that is to be recorded
     * @return string                       This is the new  file path generated
     * @return string return absolute path to the file
     */
    public function resolveStoragePath(
        ?string $mimeType,
        MediaStorageParams $params,
        ?string $fileName = null
    ): string {
        $storedFileName = $fileName ?? $params->storedFileName;

        if ($storedFileName !== null) {
            return $this->resolveFilePath($mimeType, $params, $storedFileName);
        }

        return $this->resolveDirectoryPath($mimeType, $params);
    }



    private function relativePathFrom(
        string $absolutePath,
        string $root
    ): string {
        $root = rtrim(
            $root,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($absolutePath, $root)) {
            throw new \RuntimeException(
                sprintf(
                    'Path "%s" is outside root "%s".',
                    $absolutePath,
                    $root
                )
            );
        }

        return ltrim(
            substr($absolutePath, strlen($root)),
            DIRECTORY_SEPARATOR
        );
    }


    #[Override]
    public function resolveDirectoryPath(
        ?string $mimeType,
        MediaStorageParams $params
    ): string {
        $base = $this->appendPath(
            $this->projectDir,
            $params->scope->value,
            $params->storagePrefix,
            'uploads'
        );

        if ($params->ownerType) {
            $base = $this->appendPath($base, $params->ownerType->value);
        }

        $path = match (true) {
            str_starts_with((string) $mimeType, 'image')           => $this->appendPath($base, 'images'),
            str_starts_with((string) $mimeType, 'video')           => $this->appendPath($base, 'videos'),
            str_starts_with((string) $mimeType, 'audio')           => $this->appendPath($base, 'audios'),
            str_starts_with((string) $mimeType, 'application/pdf') => $this->appendPath($base, 'documents'),
            default                                                => $this->appendPath($base, 'others'),
        };



        if ($params->ownerId) {
            $path = $this->appendPath($path, $params->ownerId);
        }

        if ($params->purpose) {
            $path = $this->appendPath($path, $params->purpose->value);
        }

        return $path;
    }


    #[Override]
    public function resolveFilePath(
        ?string $mimeType,
        MediaStorageParams $params,
        ?string $fileName = null
    ): string {
        $directory = $this->resolveDirectoryPath($mimeType, $params);
        $storedFileName = $fileName ?? $params->storedFileName;

        if ($storedFileName === null) {
            throw new \InvalidArgumentException('A file name must be provided to resolve a complete file path.');
        }

        return $this->appendPath($directory, $storedFileName);
    }



    //-- Safely Build path
    public function appendPath(string $base, string ...$segments): string
    {
        return rtrim($base, '/\\')
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR, array_map(
                fn(string $segment) => trim($segment, '/\\'),
                $segments
            ));
    }

    
    /**
     * Resolve relatif & absolute path
     */
    public function resolvePath(
        string $absolutePath,
        RootPath $rootPath
    ): string {
        $absolutePath = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $absolutePath
        );

        $projectRoot = rtrim(
            $this->projectDir,
            DIRECTORY_SEPARATOR
        );

        $publicRoot = $projectRoot
            . DIRECTORY_SEPARATOR
            . 'public';

        return match ($rootPath) {

            RootPath::SYSTEM =>
                $absolutePath,

            RootPath::PROJECT =>
                $this->relativePathFrom(
                    $absolutePath,
                    $projectRoot
                ),

            RootPath::PUBLIC =>
                $this->relativePathFrom(
                    $absolutePath,
                    $publicRoot
                ),
        };
    }


    /**
     * Return MIME type guessed by file extension.
     */
    public function resolveMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',

            'pdf' => 'application/pdf',

            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',

            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',

            default => 'application/octet-stream',
        };
    }
}