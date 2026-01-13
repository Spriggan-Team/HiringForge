<?php


namespace App\Api\DTO\User;

use App\Api\DTO\User\CreateUserRequest;
use Symfony\Component\Validator\Constraints as Assert;

class OverwriteUserRequest extends CreateUserRequest{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid,
        string $name,
        string $email,
        string $password,
        string $siret,
    ){
        parent::__construct(name: $name, email: $email, password: $password, siret: $siret);
    }
}