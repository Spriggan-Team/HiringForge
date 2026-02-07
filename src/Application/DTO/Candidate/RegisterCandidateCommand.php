<?php

namespace App\Api\DTO\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Address;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterCandidateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $firstName,

        #[Assert\NotBlank]
        public string $lastName,
        
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $password,

        public ?StaticMedia $image,
        public StaticMedia $cv,

        public Address $address
    ){}
}