<?php

namespace App\Api\Controllers\Employment\Mapper;

use App\Application\DTO\EmploymentOffer\CreateOfferRequestDto;
use Symfony\Component\HttpFoundation\Request;

class CreateEmploymentOfferMapper
{
    public function mapRequestToDto(Request $request): CreateOfferRequestDto
    {
        $data = $request->toArray();

        // Deal with expired
        $rawExpiredAt = $data['expiredAt'] ?? null;
        $expiredAt = null;

        if ($rawExpiredAt !== null && $rawExpiredAt !== 0 && $rawExpiredAt !== '0' && $rawExpiredAt !== '') {
            try {
                $expiredAt = new \DateTimeImmutable((string) $rawExpiredAt);
            } catch (\Exception) {
                $expiredAt = null;
            }
        }

        // Salary
        $rawSalary = $data['salary'] ?? null;
        $salary = ($rawSalary !== null && $rawSalary !== '') ? (float) $rawSalary : null;

        return new CreateOfferRequestDto(
            candidateId: (string) ($data['candidateId'] ?? ''),
            applicationId: (string) ($data['applicationId'] ?? ''),
            expiredAt: $expiredAt,
            jobTitle: (string) ($data['jobTitle'] ?? ''),
            title: isset($data['title']) ? (string) $data['title'] : null,
            salary: $salary,
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }
}