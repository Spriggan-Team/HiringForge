<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\Company;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Exception\RessourceNotFound;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;

use Override;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class CompanyRepository extends ServiceEntityRepository 
    implements CompanyRepositoryInterface
{
    public function __construct(
        private ManagerRegistry $registery,
        private CompanyEntityMapper $mapper,

    ){
        parent::__construct($registery, CompanyEntity::class);
    }


    #[Override]
    public function exists(string $companyName): bool
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.name = :name')
            ->setParameter('name', $companyName)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }


    #[Override]
    public function save(Company $company): void
    {
        $repository = $this->em->getRepository(CompanyEntity::class);
        $entity = $repository->findOneBy(['name' => $company->name()]);

        if (!$entity) {
            $entity = $this->mapper->toDoctrineEntity($company, $this);
            $this->em->persist($entity);
        }
        else {
             $this->mapper->copy($company, $entity);
        }

        $this->em->flush();
    }


    #[Override]
    public function get(string $companyId): Company
    {
        $entity = $this->find($companyId);
        if(!$entity){
            throw new RessourceNotFound("Company not found");
        }
        $domain = $this->mapper->toDomainEntity($entity);
        return $domain;
    }


    #[Override]
    public function containsUser(string $userId, string $companyId): bool
    {
        throw new \Exception('Not implemented');
    }

}