<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\User\User as DomainEntity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\User\UserRepositoryInterface;


use Doctrine\ORM\EntityManagerInterface;


class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    
    public function findAll(): array
    {
        $collection = $this->manager->getRepository(UserEntity::class)->findAll();
        for ($i=0 ; $i < count($collection); $i++) { 
            $collection[$i] = UserEntityMapper::toDomainEntity($collection[$i]);            
        }
        return $collection;
    }



    public function findById(string $uuid): ?DomainEntity
    {
        $entity = $this->manager->find(UserEntity::class, $uuid);
        if(!$entity){
            throw new RessourceNotFound();
        }
        return UserEntityMapper::toDomainEntity($entity);
    }

    
    public function findByEmail(string $email): ?DomainEntity
    {
        throw new \Exception('Not implemented');
    }


    public function save(DomainEntity $user): void
    {
        $entity = UserEntityMapper::toDoctrineEntity($user);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    
    public function delete(string $uuid): void
    {
        $entity = $this->manager->find(UserEntity::class, $uuid);
        if(!$entity)
            throw new RessourceNotFound();

        $this->manager->remove($entity);
        $this->manager->flush();
    }

}
