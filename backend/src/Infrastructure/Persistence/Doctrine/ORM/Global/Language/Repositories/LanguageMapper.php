<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\Repositories;

use App\Domain\Shared\Language\Language;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity as Entity;

final class LanguageMapper{

    public function toDomain(Entity $entity): Language
    {
        $domain = new Language(
            id: $entity->getId(),
            code: $entity->getCode(),
            label: $entity->getLabel()    
        );
        return $domain;
    }

}