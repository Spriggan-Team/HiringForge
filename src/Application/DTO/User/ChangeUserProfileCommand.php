<?php

namespace App\Application\DTO\User;

use App\Domain\Shared\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ChangeUserProfileCommand
{
    public function __construct(
        public string $uuid, // must match account id
        public ?string $name = null,
        public ?string $siret = null,
        /** @var UploadedFile[] */
        public array $addImages = [],
        /** @var string[] names of images to delete */
        public array $deleteImages = [],
        public ?Address $address = null,
        public ?UploadedFile $videoPresentation = null
    ) {}
}
