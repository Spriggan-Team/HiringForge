<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\File\MediaStorageParams;
use App\Domain\File\MediaStorageScope;
use App\Domain\File\MediaUploadResult;
use App\Domain\Shared\PathResolverInterface;

use Override;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class MediaStorage implements MediaStorageInterface
{
    /**
     * This is the project dir to the root 'src' folder
     * @property string $baseStoragePath
     */
    private string $projectDir;


    public function __construct(
        string $projectDir,
        private PathResolverInterface $pathResolver,
        private Filesystem $filesystem,
    ){
        $this->projectDir = rtrim($projectDir, '/');
    }



    public function store(
        mixed $file,
        /** @var string[] a array of filename  */
        MediaStorageParams $params,
        ?string $storedFileName = null,
        ?callable $successCallback = null, 
        ?callable $errorCallback = null,
    ): void
    {
        if(!$file instanceof UploadedFile)
        {
            throw new \Exception("[MediaStorage::strore] Such a class of file is not supported yet!!");
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname());
        $path = $this->pathResolver->resolveStoragePath(
            mimeType: $mime, 
            params: $params,
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
                return;    
            }
            throw $exception;
        }
    }



    public function read(string $name, ?string $id = null, ?MediaOwnerType $ownerType = null, ?MediaPurpose $purpose = null): mixed
    {
        throw new \Exception('Not implemented');
    }


    /**
     * Removes files & directories
     */
    public function remove(
        MediaStorageParams $params,
        ?string $fileName = null,
        ?string $mimeType = null,
        bool $recursive = false,
        ?callable $successCallback = null, 
        ?callable $errorCallback = null,
    ): void {
        try {
            $targetFileName = $fileName ?? $params->storedFileName;

            //-- Determine mime if not provided
            if ($mimeType === null) {
                $extension = strtolower(pathinfo($params->storedFileName, PATHINFO_EXTENSION));
                $mimeType = match ($extension) {
                    'jpg', 'jpeg', 'png', 'webp', 'gif' => 'image/',
                    'mp4', 'mkv', 'avi'                => 'video/',
                    'mp3', 'wav'                       => 'audio/',
                    'pdf'                              => 'application/pdf',
                    default                            => null,
                };
            }

            // Idempotent Handling of Missing Files
            $fullFilePath = $this->pathResolver->resolveFilePath(
                mimeType: $mimeType,
                params: $params,
                fileName: $targetFileName
            );

            if ($this->filesystem->exists($fullFilePath)) {
                $this->filesystem->remove($fullFilePath);

                if ($recursive) {
                    $directoryPath = dirname($fullFilePath);
                    $this->removeEmptyParentDirectories($directoryPath, $params->scope);
                }
            }

            //-- sucess callback
            if ($successCallback) {
                $successCallback($params->storedFileName);
            }

        }
        catch (\Exception $exception) {
            //-- callback
            if ($errorCallback) {
                $errorCallback($exception);
                return;
            }

            throw $exception;
        }
    }



    private function removeEmptyParentDirectories(
        string $directoryPath,
        MediaStorageScope $scope
    ): void {
        $projectDir = realpath($this->projectDir . '/' . $scope->value);
        $currentPath = realpath($directoryPath);

        if ($projectDir === false || $currentPath === false) {
            return;
        }

        $normalizedBase = rtrim($projectDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        while (
            $currentPath !== $projectDir &&
            str_starts_with($currentPath . DIRECTORY_SEPARATOR, $normalizedBase)
        ) {
            $items = @scandir($currentPath);
            if ($items === false) {
                break;
            }

            $items = array_diff($items, ['.', '..']);
            if (!empty($items)) {
                break; // Le dossier n'est pas vide
            }

            $parentPath = dirname($currentPath);

            if (!@rmdir($currentPath)) {
                break;
            }

            // clear cache of function of file status
            clearstatcache(true, $parentPath);

            $currentPath = realpath($parentPath);
            if ($currentPath === false) {
                break;
            }
        }
    }

    

    /** Strore temp file */
    #[Override]
    public function storeTemp(mixed $file, ?string $storedFileName = null): string
    {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException("[MediaStorage::storeTemp] Unsupported file class.");
        }

        $sysTempDir = sys_get_temp_dir() . '/app_uploads';
        if (!$this->filesystem->exists($sysTempDir)) {
            $this->filesystem->mkdir($sysTempDir, 0775);
        }

        $extension = $file->guessExtension() ?: 'bin';
        $tempFileName = ($storedFileName ?? Uuid::v4()->toRfc4122()) . '.' . $extension;
        $targetPath = $sysTempDir . '/' . $tempFileName;

        // Deplace file to temporary folder
        $file->move($sysTempDir, $tempFileName);

        return $targetPath;
    }
    

    /** Delete temp path */
    #[Override]
    public function deleteTemp(string $path): void
    {
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }


    #[Override]
    public function moveToFinal(string $tempPath, MediaStorageParams $params): void
    {
        if (!$this->filesystem->exists($tempPath)) {
            throw new FileNotFoundException("Temporary file missing: {$tempPath}");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tempPath);

        //-- Determine directory path 
        $finalDirectory = $this->pathResolver->resolveDirectoryPath(
            mimeType: $mime,
            params: $params
        );

        //-- Determine path
        $fileName = $params->storedFileName ?? basename($tempPath);

        $finalPath = $this->pathResolver->resolveFilePath(
            mimeType: $mime,
            params: $params,
            fileName: $fileName
        );

        // 2. Création du dossier parent si nécessaire
        if (!$this->filesystem->exists($finalDirectory)) {
            $this->filesystem->mkdir($finalDirectory, 0775);
        }

        // 3. Déplacement du fichier
        $this->filesystem->rename($tempPath, $finalPath, true);
    }
}