<?php

namespace App\Application\DTO;

use App\Domain\Shared\Account\AccountRole;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


class AuthentificateAccount 
{
    public function __construct(
            #[Assert\NotBlank]
            #[Assert\Email]
            public(set) string $email,

            #[Assert\NotBlank]
            public string $password,

    ){}

}

//DTO: Data Transfer Object