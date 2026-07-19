<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department\Repositories;

use App\Domain\Department\Department;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity as Entity;

final class DepartmentMapper
{

    public function toDomain(Entity $entity): Department
    {
        $domain = new Department(id: $entity->getId(), label: $entity->getLabel());
        return $domain;
    }
}