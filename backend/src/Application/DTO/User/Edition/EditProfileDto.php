<?php


namespace App\Application\DTO\User\Edition;

use App\Application\DTO\Company\EditCompanyDto;

final class EditProfileDto
{
    public ?EditCompanyDto $company = null;
    public ?EditUserDto $user = null;
}
