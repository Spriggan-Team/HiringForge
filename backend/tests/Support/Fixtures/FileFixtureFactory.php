<?php

namespace App\Tests\Support\Fixtures;


use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileFixtureFactory
{
    public static function createFakeImage(): UploadedFile
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test_img_');
        // Génère une image PNG minimale valide de 1x1 px
        file_put_contents($filePath, base64_decode('iVBORw0KGgoAAAANSU8PUAAABJRU5ErkJggg=='));

        return new UploadedFile(
            path: $filePath,
            originalName: 'avatar.png',
            mimeType: 'image/png',
            error: UPLOAD_ERR_OK,
            test: true
        );
    }

    public static function createFakePdf(): UploadedFile
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test_cv_');
        file_put_contents($filePath, '%PDF-1.4 Fake PDF Content');

        return new UploadedFile(
            path: $filePath,
            originalName: 'resume.pdf',
            mimeType: 'application/pdf',
            error: UPLOAD_ERR_OK,
            test: true
        );
    }
}