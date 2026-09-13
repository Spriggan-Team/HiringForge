<?php


namespace App\Application\DTO\User\Edition;

use App\Application\DTO\EditImageDto;

final class EditUserDto
{
    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $description = null;

    public ?string $email = null;

    public ?EditImageDto $image = null;
}
