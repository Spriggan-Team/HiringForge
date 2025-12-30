<?php

namespace App\Infrastructure\Persistence\MySQL\Repositories;


use App\Domain\Entity\Account;
use App\Domain\ValueObject\MergeRule;
use App\Api\Exceptions\ApiRessourceNotFound;
use App\Domain\Repositories\AccountRepositoryInterface;
use App\Infrastructure\Persistence\MySQL\Doctrine\AccountEntity;
use App\Infrastructure\Persistence\MySQL\Mapper\AccountEntityMapper;

use Doctrine\ORM\EntityManagerInterface;


class AccountRepository implements AccountRepositoryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    public function getAll(): array
    {
        $collection = $this->manager->getRepository(AccountEntity::class)->findAll();
        return $collection;
    }

    public function getById(int $uuid): Account
    {
        $entity = $this->manager->find(AccountEntity::class, $uuid);
        if(!$entity){
            throw new ApiRessourceNotFound();
        }
        return AccountEntityMapper::toDomainEntity($entity);
    }


    public function save(Account $account, $rule= MergeRule::FULL_OVERWRITE ): void
    {
        $entity = $this->manager->find(Account::class, $account->getId());
        if(!$entity){
            $entity = AccountEntityMapper::toDoctrineEntity($account);
            $this->manager->persist($entity);
        }
        else{
            AccountEntityMapper::mergeIntoDoctrineEntity($account, $entity, $rule);
        }
        $this->manager->flush();
    }
    
    public function delete(string $uuid): void
    {
        $entity = $this->manager->find(AccountEntity::class, $uuid);
        if(!$entity)
            throw new ApiRessourceNotFound();

        $this->manager->remove($entity);
        $this->manager->flush();
    }

}
