<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Repositories;


use App\Domain\User\User as DomainEntity;
use App\Api\Exceptions\ApiRessourceNotFound;
use App\Domain\User\UserRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity as ORMEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Mappers\UserEntityMapper;

use Doctrine\ORM\EntityManagerInterface;


class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    
    public function getAll(): array
    {
        $collection = $this->manager->getRepository(ORMEntity::class)->findAll();
        for ($i=0 ; $i < count($collection); $i++) { 
            $collection[$i] = UserEntityMapper::toDomainEntity($collection[$i]);            
        }
        return $collection;
    }



    public function getById(string $uuid): DomainEntity
    {
        $entity = $this->manager->find(ORMEntity::class, $uuid);
        if(!$entity){
            throw new ApiRessourceNotFound();
        }
        return UserEntityMapper::toDomainEntity($entity);
    }



    public function save(DomainEntity $user): void
    {
        $entity = $this->manager->find(ORMEntity::class, $user->id()->value());
        if(!$entity){
            $entity = UserEntityMapper::toDoctrineEntity($user);
            $this->manager->persist($entity);
        }
        else{
            UserEntityMapper::copy($user, $entity);
        }
        $this->manager->flush();
    }


    
    public function delete(string $uuid): void
    {
        $entity = $this->manager->find(ORMEntity::class, $uuid);
        if(!$entity)
            throw new ApiRessourceNotFound();

        $this->manager->remove($entity);
        $this->manager->flush();
    }

}
