<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\CompanyViewRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Override;


class CompanyViewRepository extends ServiceEntityRepository  implements CompanyViewRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ){
        parent::__construct($registry, CompanyEntity::class);
    }


    /**
     * Retrieve company data for the recruiter view.
     *
     * @return array{
     *      name: string,
     *      siret: string,
     *      description: ?string,
     *      departmentCount: int,
     *      location: array{
     *          city: string,
     *          postalCode: string,
     *          street: string,
     *          country: string
     *      },
     *      logo: ?string,
     *      videoPresentation: ?string,
     *      images: array{
     *          main: ?string,
     *          others: array<int, string>
     *      },
     * }
     */
    #[Override]
    public function getCompanyViewById(string $companyId): array
    {
        $company = $this->createQueryBuilder('c')
            ->select(
                'c.name AS name',
                'c.siret AS siret',
                'c.description AS description',
                'COUNT(DISTINCT d.id) AS departmentCount',
                'addr.street AS street',
                'addr.city AS city',
                'addr.country AS country',
                'addr.postalCode AS postalCode',
                'l.name AS logo',
                'v.name AS videoPresentation',
            )
            ->leftJoin('c.departments', 'd')
            ->leftJoin('c.logo', 'l')
            ->leftJoin('c.videoPresentation', 'v')
            ->leftJoin('c.companyAddresses', 'addr')
            ->where('c.id = :companyId')
            ->setParameter('companyId', $companyId)
            ->groupBy(
                'c.id',
                'c.name',
                'c.siret',
                'c.description',
                'addr.street',
                'addr.city',
                'addr.country',
                'addr.postalCode',
                'l.name',
                'v.name',
            )
            ->getQuery()
            ->getOneOrNullResult();

        if ($company === null) {
            throw new \RuntimeException('Company not found.');
        }

        $imageEntities = $this->companyImageRepository
            ->createQueryBuilder('ci')
            ->select('ci', 'image')
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
            $imageName = $companyImage->getImage()->getName();

            if ($companyImage->isMain()) {
                $mainImage = $imageName;
                continue;
            }

            $otherImages[] = $imageName;
        }

        return [
            'name' => $company['name'],
            'siret' => $company['siret'],
            'description' => $company['description'],
            'departmentCount' => (int) $company['departmentCount'],
            'location' => [
                'city' => $company['city'],
                'postalCode' => $company['postalCode'],
                'street' => $company['street'],
                'country' => $company['country'],
            ],
            'logo' => $company['logo'],
            'videoPresentation' => $company['videoPresentation'],
            'images' => [
                'main' => $mainImage,
                'others' => $otherImages,
            ],
        ];
    }
}