<?php


namespace App\Api\Controllers\Helpers;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;

use Symfony\Component\HttpFoundation\Request;


trait ApiControllerHelpers
{
    /**
     * Resolves the public URL for a given media file relative to the project public directory.
     */
    protected function resolveUrl(
        MediaStorageInterface $mediaStorage,
        Request $request,
        string $mimeType,
        string $fileName,
        mixed $ownerId,
        MediaPurpose $purpose,
        MediaOwnerType $ownerType,
        string $projectDir,
        string $scope = 'public'
    ): ?string {
        $directoryPath = $mediaStorage->resolveTargetDirectory(
            mimeType: $mimeType,
            ownerId: $ownerId,
            purpose: $purpose,
            ownerType: $ownerType
        );

        $fullPath = rtrim($directoryPath, '/') . '/' . $fileName;
        /** @var string $projectDir */
        $publicDir = $projectDir . '/public';

        if (str_starts_with($fullPath, $publicDir)) {
            $relativePath = substr($fullPath, strlen($publicDir));
            return $request->getUriForPath($relativePath);
        }

        return null;
    }
}