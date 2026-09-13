<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyImageEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CompanyImageRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registery
    ){
        parent::__construct($registery, CompanyImageEntity::class);
    }

    /**
     * @return FileEntity
     */
    public function getCompanyImages(string $companyId)
    {
        return $this->createQueryBuilder('ci')
            ->innerJoin('ci.image', 'image')
            ->where('ci.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->orderBy('ci.isMain', 'DESC')
            ->addOrderBy('ci.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

}