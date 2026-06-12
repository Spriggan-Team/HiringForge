<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\Company;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;


use Doctrine\ORM\EntityManagerInterface;
use Override;


class CompanyRepository implements CompanyRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    #[Override]
    public function exists(string $companyName): bool
    {
        $result = $this->em->createQueryBuilder()
            ->select('c.id')
            ->from(CompanyEntity::class, 'c')
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
            $entity = CompanyEntityMapper::toDoctrineEntity($company, $this->em);
            $this->em->persist($entity);
        }
        else {
            CompanyEntityMapper::copy($company, $entity, $this->em);
        }

        $this->em->flush();
    }
}