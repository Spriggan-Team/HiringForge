<?php

namespace App\Application\DTO\User;

use App\Domain\Shared\Address;

final class ChangeUserProfileCommand
{
    public function __construct(
        public string $uuid,

        public ?string $name = null,

        public ?string $siret = null,

        /** @var array represents an array of images to add*/
        public array $addImages = [],

        /** @var array<string> $deleteImages represents an array of images to add*/
        public array $deleteImages = [],

        /** @var array<string> $address represents an array of images to delete*/
        public ?Address $address = null,

        /** @var array<string> $presentation represents a video presentation*/
        public array $presentation = [], 
    ){}
}
