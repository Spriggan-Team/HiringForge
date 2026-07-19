<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\Repositories;

use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Contract\ContractType;

use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\ContractTypeEntity;


use Symfony\Component\Intl\Countries;
use Doctrine\ORM\EntityManagerInterface;

use Override;


final class ContractTypeRepository
    implements ContractTypeRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private ContractTypeMapper $mapper
    ){}


    #[Override]
    public function get(string $id): ContractType
    {
        $entity = $this->em
            ->getRepository(ContractTypeEntity::class)
            ->find($id);


        if(!$entity){
            throw new RessourceNotFound(
                "Contract type not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }

    
    #[Override]
    public function getAll(string $country, ?string $organizationId): array
    {
        $countryCode = null;

        $countries = array_flip(Countries::getNames('fr'));
        $cleanedCountry = trim(mb_convert_case($country, MB_CASE_TITLE, "UTF-8"));

        if (isset($countries[$cleanedCountry])) {
            $countryCode = $countries[$cleanedCountry];
        }

        $qb = $this->em->createQueryBuilder();

        return $qb->select('c')
            ->from(ContractTypeEntity::class, 'c')
            ->where('c.default = :default') // Global
            ->orWhere('c.organizationId = :orgId') // linked to this specific recruiter
            ->andWhere(
                $qb->expr()->orX(
                    'c.country = :countryCode',
                    'c.country IS NULL',
                    'c.country = :remoteCode'
                )
            )
            ->setParameter('default', true)
            ->setParameter('orgId', $organizationId)
            ->setParameter('countryCode', $countryCode)
            ->setParameter('remoteCode', 'ZZ')
            ->orderBy('c.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}