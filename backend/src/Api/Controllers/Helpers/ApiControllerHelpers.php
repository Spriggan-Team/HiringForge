<?php


namespace App\Api\Controllers\Helpers;


use App\Domain\File\MediaStorageParams;
use App\Domain\Shared\PathResolverInterface;
use App\Domain\Shared\RootPath;

use Symfony\Component\HttpFoundation\Request;


trait ApiControllerHelpers
{
    /**
     * Resolves the public URL for a given media file relative to the project public directory.
     */
    protected function resolvePublicImageUrl(
        Request $request,
        MediaStorageParams $params,
        PathResolverInterface $pathResolver,
        ?string $fileName = null,
        ?string $mime = null
    ): ?string {
        $absolutePath = $pathResolver->resolveStoragePath(
            params: $params,
            mimeType: $mime,
            fileName: $fileName,
        );

        $relativePath = $pathResolver->resolvePath(
            $absolutePath,
            RootPath::PUBLIC,
        );

        $relativePath = str_replace(
            '\\',
            '/',
            $relativePath,
        );

        $publicHttpRessourcePath =  $request->getUriForPath(
            '/' . ltrim($relativePath, '/'),
        );

        return $publicHttpRessourcePath;
    }
}