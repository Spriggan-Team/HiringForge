<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\Repositories;

use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Language\Language;
use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;


use Doctrine\ORM\EntityManagerInterface;
use Override;


final class LanguageRepository 
    implements LanguageRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private LanguageMapper $mapper
    ){}

    #[Override]
    public function get(int $id): Language
    {
        $entity = $this->em
            ->getRepository(LanguageEntity::class)
            ->find($id);


        if(!$entity){
            throw new RessourceNotFound(
                "Language not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }
}