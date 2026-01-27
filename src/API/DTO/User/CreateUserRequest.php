<?php

namespace App\Api\DTO\User;

use App\Domain\Shared\ValueObject\Address;
use Symfony\Component\Validator\Constraints as Assert;


class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $password,

        #[Assert\NotNull]
        public string $siret,

        public array $images = [],
        
        #[Assert\NotNull]
        public Address $address,
    ){}

    public function withImages($uploads): static
    {
        $this->images = $uploads;
        return $this;
    }
}
