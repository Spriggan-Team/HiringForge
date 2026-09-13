<?php

namespace App\Application\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EditImageDto
{
    public ?int $id = null;

    public ?UploadedFile $file = null;
}