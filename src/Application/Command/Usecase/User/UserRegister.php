<?php


namespace App\Application\Command\Usecase\User;

use App\Domain\User\User;
use App\Domain\Exception\EmailAlreadyRegistered;

use App\Domain\User\Siret;
use App\Domain\User\UserId;
use App\Domain\File\FilePurpose;
use App\Domain\File\FileOwnerType;

use App\Domain\File\FileStorageInterface;
use App\Domain\Shared\Actor\ActorRegister;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;
use App\Domain\User\UserRepositoryInterface;



class UserRegister
{

    public function __construct(
        private FileStorageInterface $storage,
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This is an usecase that enforce buisness requirement and then proceed with saving
     * @throws DomainException|EmailAlreadyRegistered|RessourceNotFound What is thrown when requirements are not respected
     */
    public function execute(
        string $name,
        string $siret,
        string $email,
        string $password,
        Address $address,
        /** @var StaticMedia */
        array $images,

    ): ActorRegister
    {    
        $email = new EmailAddress($email);
        $existingUser = $this->repository->findByEmail($email->value());    //check for any existing user

        if($existingUser){
            throw new EmailAlreadyRegistered("This user already exist"); 
        }

        $userId = new UserId();

        $imagesUploadResult = null;
        foreach($images as $staticImage){
            $imagesUploadResult = $this->storage->store(
                        $staticImage,
                        $userId->value(),
                        FileOwnerType::USER,
                        FilePurpose::PROFILE
                    );
        }

        $user =  User::create(       
            userId: $userId,
            name:   $name,
            email:  $email,
            passwordHash: $this->hasher->hash((new PlainPassword($password))->value()),
            siret:  new Siret($siret),
            images: $imagesUploadResult ? $imagesUploadResult->stored['success'] : [],
            address: $address,
        );

        
        $this->repository->save($user);
        
        return new ActorRegister(
            $userId->value(), 
            $imagesUploadResult ? $imagesUploadResult->stored['success'] : []
        );
    }
}