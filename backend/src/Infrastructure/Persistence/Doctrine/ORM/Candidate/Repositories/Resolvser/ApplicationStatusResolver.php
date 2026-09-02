<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories\Resolvser;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Domain\Interviews\InterviewStatus;

final class ApplicationStatusResolver
{
    /**
     * Resolve the effective application status from related entities.
     */
    public function resolve(
        JobApplicationStatus $applicationStatus,
        array $interviewStatuses,
        array $employmentOfferStatuses
    ): JobApplicationStatus {
        // Employment offers have the highest priority.

        if (in_array(
            EmploymentOfferStatus::ACCEPTED,
            $employmentOfferStatuses,
            true
        )) {
            return JobApplicationStatus::OFFER_ACCEPTED;
        }

        if (in_array(
            EmploymentOfferStatus::DECLINED,
            $employmentOfferStatuses,
            true
        )) {
            return JobApplicationStatus::OFFER_DECLINED;
        }

        if (in_array(
            EmploymentOfferStatus::SENT,
            $employmentOfferStatuses,
            true
        )) {
            return JobApplicationStatus::OFFER_PENDING;
        }

        // Interview states.

        if (in_array(
            InterviewStatus::IN_PROGRESS,
            $interviewStatuses,
            true
        )) {
            return JobApplicationStatus::IN_INTERVIEW;
        }

        if (in_array(
            InterviewStatus::SCHEDULED,
            $interviewStatuses,
            true
        )) {
            return JobApplicationStatus::INTERVIEW_SCHEDULED;
        }

        // Fall back to the application native status.

        return $applicationStatus;
    }
}