<?php


namespace App\Application\Command\Usecase\User;


use App\Domain\User\User;

use App\Api\DTO\User\CreateUserRequest;
use App\Domain\Services\FileStorage\FileStorageInterface;
use App\Domain\User\UserRepositoryInterface;

class UserRegister
{

    public function __construct(private UserRepositoryInterface $repository, private FileStorageInterface $storage){}

    public function execute(CreateUserRequest $command)
    {        
        $user =  User::create(
            name:   $command->name,
            email:  $command->email,
            password: $command->password,
            siret:  $command->siret,
            images: $fileNames,
            address:$command->address
        );

        $fileNames = $this->storage->store($command->images);

        $this->repository->save($user);
    }
}