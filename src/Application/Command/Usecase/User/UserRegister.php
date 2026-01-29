<?php


namespace App\Application\Command\Usecase\User;


use App\Domain\User\User;

use App\Api\DTO\User\CreateUserRequest;

use App\Domain\Services\FileStorage\FileOwnerType;
use App\Domain\Services\FileStorage\FilePurpose;
use App\Domain\Services\FileStorage\FileStorageInterface;

use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;

class UserRegister
{

    public function __construct(private UserRepositoryInterface $repository, private FileStorageInterface $storage){}

    public function execute(CreateUserRequest $command)
    {    
        $userId = new UserId();

        $fileNames = $this->storage->store(
            $command->images,
            $userId->value(),
            FileOwnerType::USER,
            FilePurpose::PROFILE_IMAGE
        );

        $user =  User::create(
            userId: $userId,
            name:   $command->name,
            email:  $command->email,
            password: $command->password,
            siret:  $command->siret,
            images: $fileNames,
            address: $command->address
        );


        $this->repository->save($user);
    }
}