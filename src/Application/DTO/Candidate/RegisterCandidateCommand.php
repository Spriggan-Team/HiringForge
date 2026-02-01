<?php

namespace App\Api\DTO\Candidate;

use App\Domain\Shared\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        public ?UploadedFile $image,
        public ?UploadedFile $cv,

        public Address $address
    ){}
}