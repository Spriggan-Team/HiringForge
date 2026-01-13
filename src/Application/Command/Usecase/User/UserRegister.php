<?php


namespace App\Application\Command\Usecase\User;

use Ramsey\Uuid\Uuid;

use App\Domain\Entity\User;
use App\Api\DTO\User\CreateUserRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;


class UserRegister
{

    public function __construct(private UserRepository $repository){}

    public function execute(CreateUserRequest $command)
    {
        $user = new User(
            id: Uuid::uuid4(),
            name:  $command->name,
            email: $command->email,
            password: $command->password,
            siret: $command->siret,
        );
        $this->repository->save($user);
    }
}