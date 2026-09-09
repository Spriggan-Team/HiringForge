<?php

namespace App\Domain\Company;

interface CompanyViewRepositoryInterface
{
    /**
     * Retrieve company data for the recruiter view.
     * @return array{
     *      name: string,
     *      siret: string,
     *      description: string,
     *      departmentCount: int,
     *      location: array{
     *          city: string,
     *          postalCode: string,
     *          street: string,
     *          country: string
     *      },
     *      logo: string,
     *      videoPresntation: string,
     *      images: array{
     *          main?: string,
     *          others: array<int, string>
     *      },
     * }
     * @throws \Exception|\RuntimeException
     */
    public function getCompanyViewById(string $companyId): array;
}