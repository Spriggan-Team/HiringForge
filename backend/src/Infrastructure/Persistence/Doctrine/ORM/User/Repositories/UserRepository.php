<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Domain\Exception\ResourceCreationRejected;
use App\Domain\User\User as DomainEntity;
use App\Domain\Sharedp\KnownIdentity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Account\AccountRole;
use App\Domain\User\UserRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;


use Doctrine\ORM\EntityManagerInterface;
use Override;



class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $em){}

    #[Override]
    public function assertExist(?string $uuid = null, ?string $email = null): KnownIdentity
    {
        $criteria = [];
        if($uuid)
            $criteria["id"] = $uuid;
        else if($email)
            $criteria["email"] = $email;

        $accountRepository = $this->em->getRepository(AccountEntity::class);
        $userRepository = $this->em->getRepository(UserEntity::class);

        $account = $accountRepository->findOneBy($criteria);
        if(!$account)
            throw new RessourceNotFound();

        $user = $userRepository->find($account->getId());
        if(!$user)
            throw new RessourceNotFound();

        return new KnownIdentity(
            uuid: $user->getId(),
            email: $user->getEmail(),
            password: $user->getPassword(),
            role: AccountRole::USER
        );
    }


    public function findById(string $uuid): DomainEntity
    {
        $entity = $this->em->find(UserEntity::class, $uuid);
        if(!$entity){
            throw new RessourceNotFound("[USER] This id is not registered");
        }
        return UserEntityMapper::toDomainEntity($entity);
    }

    

    public function findByEmail(string $email): DomainEntity
    {
        $entity = $this->em->getRepository(UserEntity::class)->findOneBy([
            "email" => $email
        ]);
        if(!$entity){
            throw new RessourceNotFound("[USER] This email belogns to no user");
        }
        return UserEntityMapper::toDomainEntity($entity);
    }


    /**
     * @throws ResourceCreationRejected
     * @return void
     */
    public function save(DomainEntity $user): void
    {
        try{
            $entity = UserEntityMapper::toDoctrineEntity($user, $this->em);
            $this->em->persist($entity);
            $this->em->flush();
        }
        catch(\Exception){
            throw new ResourceCreationRejected();
        }
    }

    

    public function delete(string $uuid): void
    {
        $entity = $this->em->find(UserEntity::class, $uuid);
        if(!$entity){
            throw new RessourceNotFound("This ressource does not exist");
        }
        $this->em->remove($entity);
        $this->em->flush();
    }


    public function change(DomainEntity $user, string $uuid, ?array $deleteImages=null): void
    {
        throw new \Exception('Not implemented');
    }

}
