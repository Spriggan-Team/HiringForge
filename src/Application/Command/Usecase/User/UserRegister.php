<?php


namespace App\Application\Command\Usecase\User;

use App\Domain\User\User;
use App\Application\DTO\User\RegisterUserCommand;
use App\Domain\Exception\EmailAlreadyRegistered;

use App\Domain\User\Siret;
use App\Domain\User\UserId;
use App\Domain\File\FilePurpose;
use App\Domain\File\FileOwnerType;

use App\Domain\File\FileStorageInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\User\UserRepositoryInterface;



class UserRegister
{

    public function __construct(
        private FileStorageInterface $storage,
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher
    ){}

    public function execute(RegisterUserCommand $command): array
    {    
        $existingUser = $this->repository->findByEmail($command->email);

        if($existingUser){
            throw new EmailAlreadyRegistered("This user already exist"); 
        }

        $userId = new UserId();

        $images = $this->storage->store(
            $command->images,
            $userId->value(),
            FileOwnerType::USER,
            FilePurpose::PROFILE_IMAGE);

        $user =  User::create(       
            userId: $userId,
            name:   $command->name,
            email:  new EmailAddress($command->email),
            passwordHash: $this->hasher->hash($command->password),
            siret:  new Siret($command->siret),
            images: $images["succeed"],
            address: $command->address,
        );

        
        $this->repository->save($user);
        
        return [$userId->value(), $images->failed];
    }
}