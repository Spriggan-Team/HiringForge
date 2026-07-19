<?php

namespace App\Api\Controllers\User\JobOffer\Mapper;


use App\Application\DTO\JobOffer\SalaryRequest;
use App\Application\DTO\JobOffer\CreateJobOfferRequest;
use App\Application\DTO\JobOffer\RequiredLanguageRequest;


final class CreateJobOfferRequestMapper
{
    public function fromArray(array $body): CreateJobOfferRequest
    {
        return new CreateJobOfferRequest(
            title: $body['title'],
            content: $body['content'],

            categories: $body['categories'] ?? [],

            languages: array_map(
                fn(array $language) =>
                    new RequiredLanguageRequest(
                        $language['languageId'],
                        $language['level']
                    ),
                $body['languages'] ?? []
            ),

            skills: $body['skills'] ?? [],

            contractTypeId: $body['contractTypeId'] ?? null,
            departmentId: $body['departmentId'] ?? null,
            workMode: $body['workMode'] ?? null,
            expertise: $body['expertise'] ?? null,

            salary: isset($body['salary'])
                ? new SalaryRequest(
                    $body['salary']['min'] ?? null,
                    $body['salary']['max'] ?? null,
                    $body['salary']['currency'] ?? "EUR"
                )
                : null,

            visibilityStatus: $body['visibilityStatus'] ?? null,
            publicationDate: null
        );
    }
}