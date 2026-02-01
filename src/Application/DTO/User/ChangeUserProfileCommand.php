<?php

namespace App\Application\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangeUserProfileCommand
{
    #[Assert\NotBlank]
    public string $uuid;

    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
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
