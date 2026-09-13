<?php

namespace App\Domain\Company;

interface CompanyViewRepositoryInterface
{

    /**
     * Retrieve company data for the recruiter view.
     *
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
     * @throws \Exception|\RuntimeException
     */
    public function getCompanyViewById(string $companyId): array;
}