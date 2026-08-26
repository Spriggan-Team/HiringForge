<?php


namespace App\Domain\Notification;


final readonly class EmploymentOfferNotificationData implements NotificationDataInterface
{
    public function __construct(
        public string $employmentOfferId,
        public string $jobTitle,
        public string $companyName,
        public ?int $salary = null,
        public ?string $expiresAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'employmentOfferId'  => $this->employmentOfferId,
            'job_title'    => $this->jobTitle,
            'company_name' => $this->companyName,
            'salary'       => $this->salary,
            'expires_at'   => $this->expiresAt,
        ];
    }
}