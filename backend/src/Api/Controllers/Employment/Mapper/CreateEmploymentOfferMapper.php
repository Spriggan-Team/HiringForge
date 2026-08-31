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
        $expiredAt = $this->parseDate($rawExpiredAt);

    
        $rawScheduledEndDate = $data['scheduledEndDate'] ?? null;
        $scheduledEndDate = $this->parseDate($rawScheduledEndDate);

        // Salary
        $rawSalary = $data['salary'] ?? null;
        $salary = ($rawSalary !== null && $rawSalary !== '') ? (float) $rawSalary : null;

        return new CreateOfferRequestDto(
            candidateId: (string) ($data['candidateId'] ?? ''),
            applicationId: (string) ($data['applicationId'] ?? ''),
            jobTitle: (string) ($data['jobTitle'] ?? ''),
            title: isset($data['title']) ? (string) $data['title'] : null,
            salary: $salary,
            message: isset($data['message']) ? (string) $data['message'] : null,
            
            expiredAt: $expiredAt,
            scheduledEndDate: $scheduledEndDate
        );
    }

    private function parseDate(?string $raw){
        if ($raw !== null && $raw !== 0 && $raw !== '0' && $raw !== '') {
            try{
                return new \DateTimeImmutable((string) $raw);
            }
            catch(\Exception){
                return null;
            }
        }
    }
}