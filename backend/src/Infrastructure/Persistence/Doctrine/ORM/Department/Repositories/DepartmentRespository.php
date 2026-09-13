<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department\Repositories;

use App\Domain\Department\Department;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Domain\Exception\ResourceNotFoundException;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;

use Override;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;



final class DepartmentRespository extends ServiceEntityRepository
    implements DepartmentRepositoryInterface
{

    public function __construct(
        private ManagerRegistry $registery,
        private DepartmentMapper $mapper
    ){
        parent::__construct($registery, DepartmentEntity::class);
    }


    /**
     * Return all associated departments for a specific company based on its id
     * 
     * @return list<array{
     *     id: int,
     *     name: string,
     *     parentId: ?int
     * }>
     */
    #[Override]
    public function findDepartmentCollectionByCompanyId(string $companyId): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, d.label AS name, p.id AS parentId')
            ->leftJoin('d.parent', 'p') // Utiliser leftJoin si un département peut n'avoir aucun parent
            ->where('d.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getArrayResult();
    }


    #[Override]
    public function get(int $id): Department
    {
        $entity = $this->find($id);

        if(!$entity){
            throw new ResourceNotFoundException(
                "Department not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }

    #[Override]
    public function exists(int $id): bool
    {
        $result = $this->createQueryBuilder("d")
                       ->select("1")
                       ->where('d.id = :id')
                       ->setParameter('id', $id)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult();
        return $result !== null;
    }


    /**
     * @return array<int, DepartmentEntity>
     */
    #[Override]
    public function findTreeByCompany(string $companyId, bool $onlyActive  = false): array
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.children', 'c')->addSelect('c')
            ->where('d.company = :companyId')
            ->andWhere('d.parent IS NULL')
            ->setParameter('companyId', $companyId)
            ->orderBy('d.label', 'ASC');

        if ($onlyActive) {
            $qb->andWhere('d.isActive = :active')
               ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }


    
    public function save(Department $domain, bool $flush = true): Department
    {
        if ($domain->id() === null) {
            $company = $this->getEntityManager()->getReference(
                CompanyEntity::class,
                $domain->companyId()
            ) ?? null;
            
            $entity = DepartmentEntity::create(
                label: $domain->label(),
                description: $domain->description(),
                code: $domain->code(),
                isActive: $domain->isActive(),
                externalRef: $domain->externalRef(),
                company: $company
            );

            $this->getEntityManager()->persist($entity);
        }
        else {
            $entity = $this->find($domain->id());
            if ($entity === null) {
                throw new ResourceNotFoundException('Department not found.');
            }
            $this->mapper->updateEntity($entity, $domain);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        $domain->setId($entity?->getId());
        return $domain;
    }




    public function remove(Department $domain, bool $flush = true): void
    {
        $entity = $this->mapper->toEntity($domain);
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


}