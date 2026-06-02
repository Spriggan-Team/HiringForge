<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Domain\User\User as DomainEntity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Sharedp\KnownIdentity;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Override;

class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $em){}

    #[Override]
    public function exists(?string $uuid = null, ?string $email = null): KnownIdentity
    {
        throw new \Exception('Not implemented');
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



    public function save(DomainEntity $user): void
    {
        $entity = UserEntityMapper::toDoctrineEntity($user);
        $this->em->persist($entity);
        $this->em->flush();
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
