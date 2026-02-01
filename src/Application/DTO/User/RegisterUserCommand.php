<?php

namespace App\Application\DTO\User;

use App\Domain\Shared\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


class RegisterUserCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $password,

        #[Assert\NotBlank]
        public string $siret,

        public array $images = [],

        public ?UploadedFile $presentation = null,
        
        #[Assert\NotNull]
        public Address $address,

        #[Assert\NotBlank]
        public ?string $desc = null,
    ){}

    public function withImages($uploads): static
    {
        $this->images = $uploads;
        return $this;
    }
}
