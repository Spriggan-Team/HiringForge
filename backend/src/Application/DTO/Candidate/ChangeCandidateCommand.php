<?php

namespace App\Application\DTO\Candidate;

use App\Domain\Shared\Address;
use App\Domain\Shared\Skill\BasicSkillModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;


class ChangeCandidateCommand
{
    public function __construct(
        public string $id,
        
        #[Assert\NotBlank]
        public string $firstName,

        #[Assert\NotBlank]
        public string $lastName,
        
        #[Assert\Email]
        public string $email,

        
        public ?Address $address = null,
        public ?UploadedFile $image = null,
        
        public ?int $searchRadius = null,
        public ?string $description = null,

        /** @var array<int, BasicSkillModel> $skills */
        public array $skills = [],
    ){}
}
