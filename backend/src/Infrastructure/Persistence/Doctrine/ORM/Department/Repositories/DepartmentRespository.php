<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department\Repositories;

use App\Domain\Department\Department;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Domain\Exception\RessourceNotFound;


use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;

use Doctrine\ORM\EntityManagerInterface;
use Override;


final class DepartmentRespository
    implements DepartmentRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private DepartmentMapper $mapper
    ){}


    #[Override]
    public function get(int $id): Department
    {
        $entity = $this->em
            ->getRepository(DepartmentEntity::class)
            ->find($id);


        if(!$entity){
            throw new RessourceNotFound(
                "Department not found"
            );
        }


        return $this->mapper->toDomain($entity);
    }
}