<?php



namespace App\Application\DTO\Company;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EditCompanyImageDto
{
    public bool $isMain = false;

    public ?UploadedFile $file = null;
}