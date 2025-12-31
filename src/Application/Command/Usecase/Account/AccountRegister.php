<?php


namespace App\Application\Command\Usecase\Account;

use Ramsey\Uuid\Uuid;

use App\Domain\Entity\Account;
use App\Api\DTO\Account\CreateAccountRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\AccountRepository;


class AccountRegister{

    public function __construct(private AccountRepository $repository){}

    public function execute(CreateAccountRequest $command)
    {
        $account = new Account(
            id: Uuid::uuid4(),
            name:  $command->name,
            email: $command->email,
            password: $command->password,
            siret: $command->siret,
        );
        $this->repository->save($account);
    }
}