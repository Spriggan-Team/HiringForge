<?php

namespace App\Application\DTO\JobOffer;

class UpdateJobOfferRequest extends CreateJobOfferRequest
{
    public function __construct(
        public string $id,
        string $title,
        array $content,
        array $categories = [],
        array $skills = [],
        array $languages = [],
        ?string $contractTypeId = null,
        ?int $departmentId = null,
        ?string $workMode = null,
        array $location = [],
        ?string $expertise = null,
        ?SalaryRequest $salary = null,
        mixed $image = null,
        ?string $publicationStatus = null,
        ?string $visibilityStatus = null,
        ?\DateTimeImmutable $publicationDate = null,
    ) {
        parent::__construct(
            title: $title,
            content: $content,
            skills: $skills,
            categories: $categories,
            languages: $languages,
            contractTypeId: $contractTypeId,
            departmentId: $departmentId,
            workMode: $workMode,
            location: $location,
            expertise: $expertise,
            salary: $salary,
            image: $image,
            publicationDate: $publicationDate,
            publicationStatus: $publicationStatus,
            visibilityStatus: $visibilityStatus,
        );
    }
}