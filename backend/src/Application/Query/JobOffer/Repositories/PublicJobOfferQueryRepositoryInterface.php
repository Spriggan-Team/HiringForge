<?php

namespace App\Application\Query\JobOffer\Repositories;



interface PublicJobOfferQueryRepositoryInterface
{

    /**
     * Récupère la liste résumée et paginée des offres d'emploi publiques.
     *
     * @return array{
     *     items: array<int, array{
     *         id: string,
     *         title: string,
     *         content: array,
     *         jobWorkMode: string|null,
     *         salary: array{min: float|null, max: float|null, currency: string|null},
     *         contractType: array{id: int, label: string}|null,
     *         location: array{street: string|null, postalCode: string|null, city: string|null, country: string|null}|null,
     *         mainImage: string|null
     *     }>,
     *     total: int,
     *     limit: int,
     *     skip: int
     * }
     */
    public function fetchPublicJobOffersSummary(
        string $locale = 'fr',
        ?string $search = null,
        ?string $address = null,
        int $limit = 10,
        int $skip = 0
    ): array;

    /**
     * Retrieves the full details of a public job posting by its ID.
     *
     * @return array{
     *     id: string,
     *     title: string,
     *     content: array,
     *     jobWorkMode: string|null,
     *     expertise: string|null,
     *     salary: array{min: float|null, max: float|null, currency: string|null},
     *     contractType: array{id: int, label: string}|null,
     *     location: array{street: string|null, postalCode: string|null, city: string|null, country: string|null}|null,
     *     skills: array<int, array{id: string, name: string, isRequired: bool}>,
     *     languages: array<int, array{id: int, name: string, level: string}>,
     *     images: array<int, string>
     * }|null
     */
    public function fetchPublicJobOfferDetail(
        string $jobOfferId,
        string $locale = 'fr'
    ): ?array;


}