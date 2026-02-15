<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\User\User as DomainEntity;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\EmailAddress;
use App\Domain\Sharedp\KnownIdentity;
use App\Domain\User\UserListItem;
use App\Domain\User\UserRepositoryInterface;


use Doctrine\ORM\EntityManagerInterface;


class UserRepository implements UserRepositoryInterface
{

    public function __construct(private EntityManagerInterface $em){}


    public function exists(?string $uuid = null, ?EmailAddress $email = null): KnownIdentity
    {
        if ($uuid) {
            $user = $this->em->find(UserEntity::class, $uuid);
        }
        elseif ($email) {
            $user = $this->em->getRepository(UserEntity::class)->findOneBy(['email' => $email]);
        }
        else {
            throw new \InvalidArgumentException('UUID or Email must be provided');
        }

        if (!$user) {
            throw new \DomainException('User not found');
        }

        return new KnownIdentity($user->getId(), $user->getRoles(), $user->getPassword());
    }
    

    
    public function findAll(?int $skip=null, ?int $limit = null): array
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

    
    public function fectchUserView(string $uuid): UserListItem
    {
        throw new \Exception('Not implemented');
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


    public function changeEmail(string $email): void
    {
        throw new \Exception('Not implemented');
    }
 

    
    public function changePassword(string $email, string $hash): void
    {
        $this->em->createQueryBuilder()
                  ->update(UserEntity::class, 'u')
                  ->set("u.password", ":pwd")
                  ->where("u.id = :id")
                  ->setParameter("pwd", $hash)
                  ->setParameter("id", $email)
                  ->getQuery()
                  ->execute();
    }


}
