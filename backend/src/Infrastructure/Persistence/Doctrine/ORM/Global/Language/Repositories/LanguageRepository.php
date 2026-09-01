<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\Repositories;

use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Shared\Language\Language;
use App\Domain\Shared\Language\LanguageRepositoryInterface;

use Override;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;



final class LanguageRepository extends ServiceEntityRepository
    implements LanguageRepositoryInterface
{

    public function __construct(
        private ManagerRegistry $registry,
        private LanguageMapper $mapper
    ){
        parent::__construct($registry, LanguageEntity::class);
    }


    #[Override]
    public function get(int $id): Language
    {
        $entity = $this->find($id);

        if(!$entity){
            throw new ResourceNotFoundException(
                "Language not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }


    #[Override]
    public function getAll(): array
    { 
        return array_map(
            fn (LanguageEntity $entity) => $this->mapper->toDomain($entity),
            $this->findAll()
        );
    }

    #[Override]
    public function findByCode(string $code): ?Language
    {
        $entity = $this->findOneBy(["code" => $code]);
        if(!$entity){
            return null;
        }
        $domain = $this->mapper->toDomain($entity);
        return $domain;
    }
}