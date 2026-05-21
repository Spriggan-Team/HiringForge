<?php

namespace App\Application\DTO\User;


use Symfony\Component\Validator\Constraints as Assert;

class UploadUserProfileImageCommand{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid
    ){}
}