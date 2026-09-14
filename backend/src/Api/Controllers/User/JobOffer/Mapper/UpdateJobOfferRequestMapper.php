<?php


namespace App\Api\Controllers\User\JobOffer\Mapper;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\JobOffer\RequiredLanguageRequest;
use App\Application\DTO\JobOffer\SalaryRequest;
use App\Application\DTO\JobOffer\UpdateJobOfferRequest;



class UpdateJobOfferRequestMapper
{
    public function fromArray(array $body, ?string $id = null): UpdateJobOfferRequest
    {
        return new UpdateJobOfferRequest(
            id: $id ?? $body['id'],
            title: isset($body['title']) ? trim($body['title']) : '',
            content: $body['content'] ?? [],
            categories: $body['categories'] ?? [],
            languages: array_map(
                fn(array $language) => new RequiredLanguageRequest(
                    languageId: $language['languageId'],
                    level: $language['level']
                ),
                $body['languages'] ?? []
            ),
            skills: $body['skills'] ?? [],
            contractTypeId: $body['contractTypeId'] ?? null,
            departmentId: $body['departmentId'] ?? null,
            workMode: $body['workMode'] ?? null,
            expertise: $body['expertise'] ?? null,
            location: $body['location'] ?? [],
            salary: isset($body['salary'])
                ? new SalaryRequest(
                    min: $body['salary']['min'] ?? null,
                    max: $body['salary']['max'] ?? null,
                    currency: $body['salary']['currency'] ?? "EUR"
                )
                : null,
            publicationStatus: $body['publicationStatus'] ?? null,
            visibilityStatus: $body['visibilityStatus'] ?? null,
            publicationDate: null,
        );
    }
}