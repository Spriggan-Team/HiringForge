<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\User\User as DomainEntity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\User\UserRepositoryInterface;


use Doctrine\ORM\EntityManagerInterface;


class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $em){}

    public function exists(string $uuid): array
    {
        return $this->em->createQueryBuilder()
            ->select('u.id, u.name, u.email')
            ->from(UserEntity::class, 'u')
            ->where('u.id = :id')
            ->setParameter('id', $uuid)
            ->getQuery()
            ->getSingleResult();
    }
    
    public function findAll(): array
    {
        $collection = $this->em->getRepository(UserEntity::class)->findAll();
        return $collection;
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
        if(!$email){
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

    public function changePassword(string $uuid, string $hash): void
    {
        $this->em->createQueryBuilder()
                  ->update(UserEntity::class, 'u')
                  ->set("u.password", ":pwd")
                  ->where("u.id = :id")
                  ->setParameter("pwd", $hash)
                  ->setParameter("id", $uuid)
                  ->getQuery()
                  ->execute();
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

}
