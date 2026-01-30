<?php

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActorConnextionRequest
{
    public function __construct(
            #[Assert\NotBlank]
            public string $email,

            #[Assert\NotBlank]
            #[Assert\Email]
            public string $password,
    ){}
}