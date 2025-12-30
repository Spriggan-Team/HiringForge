<?php


namespace App\Api\DTO\Account;

use Symfony\Component\Validator\Constraints as Assert;

class OverwriteAccountRequest extends CreateAccountRequest{
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