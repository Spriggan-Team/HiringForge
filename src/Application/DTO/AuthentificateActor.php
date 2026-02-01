<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;


class AuthentificateActor
{
    public function __construct(
            #[Assert\NotBlank]
            #[Assert\Email]
            public string $email,

            #[Assert\NotBlank]
            public string $password,
    ){}
}