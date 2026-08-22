<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department\Repositories;

use App\Domain\Department\Department as Domain;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity as Entity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;

final class DepartmentMapper
{

    public function toDomain(Entity $entity): Domain
    {
        return Domain::reconstitue(
            id: $entity->getId(),
            label: $entity->getLabel(),
            code: $entity->getCode(),
            parent: $entity->getParent()
                ? $this->toDomain($entity->getParent())
                : null,
            companyId: $entity->getCompany()->getId(),
            description: $entity->getDescription(),
            isActive: $entity->isActive(),
            externalRef: $entity->getExternalRef()
        );
    }

    public function toEntity(Domain $domain): Entity
    {
        $entity =  Entity::create(
            label: $domain->label(),
            description: $domain->description(),
            code: $domain->code(),
            isActive: $domain->isActive(),
            externalRef: $domain->externalRef()
        );
        return $entity;
    }

    public function updateEntity(Entity $entity, Domain $domain): void
    {
        $entity->setLabel($domain->label());
        $entity->setDescription($domain->description());
        $entity->setCode($domain->code());
        $entity->setIsActive($domain->isActive());
        $entity->setExternalRef($domain->externalRef());
    }
}