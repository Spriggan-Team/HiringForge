<?php

namespace  App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\Repositories;

use App\Domain\Shared\Skill\Skill;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Skill\SkillRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;


use Override;
use Doctrine\ORM\EntityManagerInterface;


final class SkillRepository
    implements SkillRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private SkillMapper $mapper
    ){}


    #[Override]
    public function get(string $id): Skill
    {
        $entity = $this->em
            ->getRepository(SkillEntity::class)
            ->find($id);

        if(!$entity){
            throw new RessourceNotFound(
                "Skill not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }
}