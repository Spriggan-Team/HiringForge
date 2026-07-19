<?php

namespace  App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\Repositories;

use App\Domain\Shared\Skill\Skill;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity as Entity;

final class SkillMapper
{
    
    public function toDomain(Entity $entity): Skill
    {
        $domain = new Skill(label: $entity->getLabel(), id: $entity->getId());
        return $domain;
    }

}