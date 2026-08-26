<?php

namespace App\Api\Controllers\Employment\Mapper;

use App\Application\DTO\Offer\CreateOfferRequestDto;

class CreateEmploymentOfferRequestMapper
{
    /**
     * @param array{
     *     candidateId: string,
     *     applicationId: string,
     *     expiredAt: string,
     *     title?: ?string,
     *     salary?: ?float,
     *     message?: ?string
     * } $data
     */
    public static function fromArray(array $data): CreateOfferRequestDto
    {
        return new CreateOfferRequestDto(
            candidateId: $data['candidateId'],
            applicationId: $data['applicationId'],
            expiredAt: $data['expiredAt'],
            title: $data['title'] ?? null,
            salary: isset($data['salary']) ? (float) $data['salary'] : null,
            message: $data['message'] ?? null,
        );
    }
}