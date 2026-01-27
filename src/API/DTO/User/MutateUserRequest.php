<?php

namespace App\Api\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class MutateUserRequest
{
    #[Assert\NotBlank]
    public string $uuid;

    #[Assert\Length(min: 2, max: 150)]
    public ?string $name = null;

    #[Assert\Length(min: 8)]
    public ?string $password = null;

    #[Assert\Length(min: 14, max: 14)]
    public ?string $siret = null;

    public function hasMutations(): bool
    {
        return $this->name != null ||
               $this->password != null ||
               $this->siret !=null;
    }
}
