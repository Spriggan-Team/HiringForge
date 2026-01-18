<?php


namespace App\Application\Command\Usecase\User;

use Ramsey\Uuid\Uuid;

use App\Domain\User\User;
use App\Domain\User\UserId;

use App\Api\DTO\User\CreateUserRequest;
use App\Domain\User\UserRepositoryInterface;


class UserRegister
{

    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(CreateUserRequest $command)
    {
        $user =  User::create(
            id: new UserId(Uuid::uuid4()),
            name:  $command->name,
            email: $command->email,
            password: $command->password,
            siret: $command->siret,
            imagePath: $command->imagePath,
            address: $command->address
        );
        
        $this->repository->save($user);
    }
}