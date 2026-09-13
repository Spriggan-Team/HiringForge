<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\CompanyViewRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyAddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Override;


class CompanyViewRepository extends ServiceEntityRepository  implements CompanyViewRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private DepartmentRepositoryInterface $departmentRepository,
        private CompanyImageRepository $companyImageRepository
    ){
        parent::__construct($registry, CompanyEntity::class);
    }



    /**
     * Retrieve company data for the recruiter view.
     *
     * @return array{
     *     name: string,
     *     siret: string,
     *     description: ?string,
     *     departments: array{
     *          id: int,
     *          name: string,
     *          parentId: int,
     *     },
     *     location: array<int, array{
     *         id: int,
     *         city: ?string,
     *         postalCode: ?string,
     *         street: ?string,
     *         country: ?string
     *     }>,
     *     logo: ?array{
     *          id: int,
     *          name: string,
     *          mime: string
     *      },
     *     videoPresentation: ?array{
     *          id: int,
     *          name: string,
     *          mime: string
     *     },
     *     images: array{
     *         main: ?array{
     *              id: int,
     *              name: string,
     *              mime: string,
     *          },
     *         others: array<int, array{
     *              id: int,
     *              name: string,
     *              mime: string,
     *          }>
     *     }
     * }
     */
    #[Override]
    public function getCompanyViewById(string $companyId): array
    {
        /*
        * ------------------------------------------------------------
        * Company information
        * ------------------------------------------------------------
        */
        $company = $this->createQueryBuilder('c')
            ->select(
                'c.name AS name',
                'c.siret AS siret',
                'c.description AS description',
                'l.id AS logoId',
                'l.name AS logoName',
                'l.mime AS logoMime',
                'v.id AS videoPresentationId',
                'v.name AS videoPresentationName',
                'v.mime AS videoPresentationMime',
            )
            ->leftJoin('c.logo', 'l')
            ->leftJoin('c.videoPresentation', 'v')
            ->where('c.id = :companyId')
            ->setParameter('companyId', $companyId)
            ->groupBy(
                'c.id',
                'c.name',
                'c.siret',
                'c.description',
                'l.name',
                'v.name',
            )
            ->getQuery()
            ->getOneOrNullResult();

        if ($company === null) {
            throw new \RuntimeException('Company not found.');
        }

        /*
        * ------------------------------------------------------------
        * Company Departments
        * ------------------------------------------------------------
        */
        $departments = $this->departmentRepository->findDepartmentCollectionByCompanyId($companyId);

        /*
        * ------------------------------------------------------------
        * Company locations
        * ------------------------------------------------------------
        */
        $locations = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(
                'ca.id AS id',
                'a.city AS city',
                'a.postalCode AS postalCode',
                'a.street AS street',
                'a.country AS country',
            )
            ->from(CompanyAddressEntity::class, 'ca')
            ->innerJoin('ca.address', 'a')
            ->where('ca.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getArrayResult();


        /*
        * ------------------------------------------------------------
        * Company images
        * ------------------------------------------------------------
        */
        $imageEntities = $this->companyImageRepository
            ->createQueryBuilder('ci')
            ->innerJoin('ci.image', 'image')
            ->where('ci.company = :companyId')
            ->setParameter('companyId', $companyId)
            ->orderBy('ci.isMain', 'DESC')
            ->addOrderBy('ci.id', 'ASC')
            ->getQuery()
            ->getResult();

        $mainImage = null;
        $otherImages = [];

        foreach ($imageEntities as $companyImage) {
            $image = $companyImage->getImage();
            $map = [
                'id' => $image->getId(),
                "name" => $image->getName(),
                "mime" => $image->getMime()
            ];
            if ($companyImage->isMain()) {
                $mainImage = $map;
                continue;
            }

            $otherImages[] = $map;
        }


        /*
        * ------------------------------------------------------------
        * Build view
        * ------------------------------------------------------------
        */
        return [
            'name' => $company['name'],
            'siret' => $company['siret'],
            'description' => $company['description'],

            'departments' => array_map(fn($value)=> [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'parentId' => $value["parentId"]
                ] ,
                $departments
            ),

            'location' => array_map(
                static fn (array $location): array => [
                    'id' => (int) $location['id'],
                    'city' => $location['city'],
                    'postalCode' => $location['postalCode'],
                    'street' => $location['street'],
                    'country' => $location['country'],
                ],
                $locations
            ),

            'logo' => $company["logoId"] ? 
                [
                    "id" => $company["logoId"],
                    "name" => $company['logoName'],
                    "mime" => $company['logoMime']
                ] 
                : null,

            'videoPresentation' => $company["videoPresentationId"] ?
                [
                    "id" => $company["videoPresentationId"],
                    "name" => $company['videoPresentationName'],    
                    "mime" => $company["videoPresentationMime"],
                ]: null,
                
            'images' => [
                'main' => $mainImage,
                'others' => $otherImages,
            ],
        ];
    }
}