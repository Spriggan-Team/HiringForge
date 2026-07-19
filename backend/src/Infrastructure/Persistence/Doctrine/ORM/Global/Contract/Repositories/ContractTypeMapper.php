<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\Repositories;

use App\Domain\Shared\Contract\ContractType;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\ContractTypeEntity as Entity;

final class ContractTypeMapper
{
    public function toDomain(
        Entity $entity
    ): ContractType
    {

        return ContractType::reconstitute(
            id: $entity->getId(),
            label: $entity->getLabel(),
            country: $entity->getCountry(),
            organizationId: $entity->getOrganizationId(),
            isDefault: $entity->isDefault()
        );
    }
}