<?php

namespace App\Application\Query\Usecase\User;

use App\Domain\File\StaticMedia;
/**
 * This class represents a user's profile
 */
class UserProfileResponse
{
    public function __construct(
        public string $name,
        public string $email,
        public string $siret,

        public array $job_offers,
        
        /** @var StaticMedia */
        public array $images,
    ){}
}