<?php

namespace  App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\Repositories;

use App\Domain\Shared\Skill\Skill;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;

final class SkillMapper
{
    
    public function toDomain(SkillEntity $entity, string $currentLocale = 'en'): Skill
    {
        $translation = $entity->getTranslation($currentLocale) ?? $entity->getDefaultTranslation();

        // Aliases
        $aliases = [];
        foreach ($entity->getSkillAliases() as $aliasEntity) {
            $aliases[] = $aliasEntity->getAlias();
        }

        // Create: With Name Constructor
        return Skill::create(
            name: $translation?->getName() ?? $entity->getCanonicalName(),
            slug: $translation?->getSlug() ?? '',
            canonicalName: $entity->getCanonicalName(),
            locale: $currentLocale,
            aliases: $aliases,
            escoUri: $entity->getEscoUri(),
            onetCode: $entity->getOnetCode(),
            isDefault: $entity->isDefault(),
            id: (string) $entity->getId()
        );
    }

}