<?php

namespace App\Application\DTO\User\Edition;

use App\Domain\Shared\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ChangeUserProfileCommand
{
    public function __construct(
        public string $uuid, // must match account id
        public EditProfileDto $profile,
    ) {}
}
