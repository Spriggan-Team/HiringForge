<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\Repositories;

use App\Domain\Exception\ResourceNotFoundException;
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
            throw new ResourceNotFoundException(
                "Contract type not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }

    #[Override]
    public function exists(int $id): bool
    {
        $result = $this->em
                       ->createQueryBuilder()
                       ->select("1")
                       ->from(ContractTypeEntity::class, "c")
                       ->where("c.id = :id")
                       ->setParameter('id', $id)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult();
        return $result !== null;
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
                //  global OR organisation owner
                ->where('(c.default = :default OR c.organizationId = :orgId)')
                // country specify or not (& global)
                ->andWhere('(c.country = :countryCode OR c.country IS NULL OR c.country = :remoteCode)')
                ->setParameter('default', true)
                ->setParameter('orgId', $organizationId)
                ->setParameter('countryCode', $countryCode)
                ->setParameter('remoteCode', 'ZZ')
                ->orderBy('c.label', 'ASC')
                ->getQuery()
                ->getArrayResult();
    }
}