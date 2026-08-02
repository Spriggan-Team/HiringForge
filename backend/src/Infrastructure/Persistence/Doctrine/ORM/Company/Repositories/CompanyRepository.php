<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\Company;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Exception\RessourceNotFound;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;

use Override;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;



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


    #[Override]
    public function isAddressOwnedByUserCompany(string $addressId, string $userId): bool
    {
        return $this->createQueryBuilder('c')
            ->select('1')
            ->innerJoin('c.users', 'u')
            ->innerJoin('c.companyAddresses', 'ca')
            ->where('u.id = :userId')
            ->andWhere('ca.address = :addressId')
            ->setParameter('userId', $userId)
            ->setParameter('addressId', $addressId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }


        
    #[Override]
    public function fetchUserCompanyProjection(
        string $userId,
        array $scheme = ['id' => true]
    ): ?array {
        $allowedCompanyFields = ['id', 'name', 'siret'];
        $allowedAddressFields = [ 'id','city', 'country', 'postalCode', 'address'];

        $selects = [];
        $needsAddressJoin = false;
        $addressLimit = null;

        // Analizing (scheme)
        foreach ($scheme as $fieldKey => $value) {
            if (!$value) {
                continue;
            }

            // Company's Direct Fields
            if (in_array($fieldKey, $allowedCompanyFields, true)) {
                $selects[] = "c.$fieldKey AS $fieldKey";
                continue;
            }

            // Configuring the Address Limit : "address[limit]" => 1
            if ($fieldKey === 'address[limit]') {
                $needsAddressJoin = true;
                $addressLimit = (int) $value;
                continue;
            }

            // Fields Related to the Address: "address[city]" => true
            if (str_starts_with($fieldKey, 'address[')) {
                $needsAddressJoin = true;

                // Extracting the field name (ex: "city" depuis "address[city]")
                preg_match('/address\[([a-zA-Z0-9_]+)\]/', $fieldKey, $matches);
                $subField = $matches[1] ?? null;

                if ($subField && in_array($subField, $allowedAddressFields, true)) {
                    $selects[] = "a.$subField AS address_$subField";
                }
            }
        }

        // Default fallback if no valid field is requested
        if (empty($selects)) {
            $selects = ['c.id AS id'];
        }

        // Query building
        $qb = $this->createQueryBuilder('c')
            ->select(implode(', ', $selects))
            ->innerJoin('c.recruiters', 'r')
            ->where('r.id = :userId')
            ->setParameter('userId', $userId);

        // Conditional Joins by Address
        if ($needsAddressJoin) {
            $qb->leftJoin('c.companyAddresses', 'ca')
            ->leftJoin('ca.address', 'a');

            // Apply the limit if specified
            if ($addressLimit !== null && $addressLimit > 0) {
                $qb->setMaxResults($addressLimit);
            }
        }

        $rawResults = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        if (empty($rawResults)) {
            return null;
        }

        // Result Formatting
        $formattedResult = [];
        $firstRow = $rawResults[0];

        // Company fields
        foreach ($firstRow as $key => $val) {
            if (!str_starts_with($key, 'address_')) {
                $formattedResult[$key] = $val;
            }
        }

        // Reconstruction of Address Data
        if ($needsAddressJoin) {
            $extractedAddresses = [];

            foreach ($rawResults as $row) {
                $addressData = [];
                foreach ($row as $key => $val) {
                    if (str_starts_with($key, 'address_')) {
                        $cleanKey = str_replace('address_', '', $key);
                        $addressData[$cleanKey] = $val;
                    }
                }
                if (!empty(array_filter($addressData))) {
                    $extractedAddresses[] = $addressData;
                }
            }

            // if limit is equal to1, we returned address as an objet (single array)
            if ($addressLimit === 1) {
                $formattedResult['address'] = $extractedAddresses[0] ?? null;
            }
            else {
                // Otherwise we returned an array of addresses
                $formattedResult['addresses'] = $extractedAddresses;
            }
        }

        return $formattedResult;
    }
}