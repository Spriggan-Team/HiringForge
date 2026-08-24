<?php

namespace App\Application\DTO\Candidate;

use App\Domain\File\StaticMedia;
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

        public ?Address $address,

        public ?int $searchRadius,

        
        public string $verificationCode ,
        public ?string $description = null,
    ){}
}