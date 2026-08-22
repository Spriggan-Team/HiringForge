<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\Repositories;


use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\CategoryEntity;
use Doctrine\ORM\EntityManagerInterface;



final class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryEntityMapper $mapper
    ){}


    public function getExistingByIds(array $ids): array
    {
        if(empty($ids)){
            return [];
        }

        $entities = $this->em
            ->getRepository(CategoryEntity::class)
            ->findBy([
                "id" => $ids
            ]);


        if(count($entities) !== count($ids)){
            throw new \DomainException(
                "Some categories do not exist"
            );
        }

        return array_map(
            fn(CategoryEntity $entity)
                => $this->mapper->toDomain($entity),
            $entities
        );
    }
}