<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories\Resolvser;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationMenu;
use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Domain\Interviews\InterviewStatus;


final class ApplicationStatusResolver
{
    /**
     * Resolve the effective application status from related entities.
     * @return array<int, JobApplicationStatus>
     */
    public function resolve(
        JobApplicationStatus $applicationStatus,
        array $interviewStatuses,
        array $employmentOfferStatuses
    ): array {
        $resolvedStatuses = [];
        if (in_array(EmploymentOfferStatus::ACCEPTED, $employmentOfferStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::OFFER_ACCEPTED;
        }

        if (in_array(EmploymentOfferStatus::DECLINED, $employmentOfferStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::OFFER_DECLINED;
        }

        if (in_array(EmploymentOfferStatus::EXPIRED, $employmentOfferStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::OFFER_EXPIRED; 
        }

        if (in_array(EmploymentOfferStatus::SENT, $employmentOfferStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::OFFER_PENDING;
        }

        // 2. Interview states resolution
        if (in_array(InterviewStatus::IN_PROGRESS, $interviewStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::IN_INTERVIEW;
        }

        if (in_array(InterviewStatus::SCHEDULED, $interviewStatuses, true)) {
            $resolvedStatuses[] = JobApplicationStatus::INTERVIEW_SCHEDULED;
        }

        // 3. Fallback to native application status if no secondary status was triggered
        if (empty($resolvedStatuses)) {
            $resolvedStatuses[] = $applicationStatus;
        }

        // Return unique values to avoid any accidental duplication
        return array_values(array_unique($resolvedStatuses, SORT_REGULAR));
    }


        
    /**
     * Check whether an application status belongs to a menu section.
     */
    public function matchesMenu(
        JobApplicationStatus $status,
        ApplicationMenu $sectionType
    ): bool {
        return match ($sectionType) {
            ApplicationMenu::ALL => true,
            ApplicationMenu::PENDING =>
                !in_array(
                    $status,
                    [
                        JobApplicationStatus::INTERVIEW_SCHEDULED,
                        JobApplicationStatus::IN_INTERVIEW,
                        JobApplicationStatus::OFFER_ACCEPTED,
                        JobApplicationStatus::OFFER_DECLINED,
                    ],
                    true
                ),

            ApplicationMenu::INTERVIEW =>
                in_array(
                    $status,
                    [
                        JobApplicationStatus::INTERVIEW_SCHEDULED,
                        JobApplicationStatus::IN_INTERVIEW,
                    ],
                    true
                ),

            ApplicationMenu::COMPLETED =>
                in_array(
                    $status,
                    [
                        JobApplicationStatus::OFFER_ACCEPTED,
                        JobApplicationStatus::OFFER_DECLINED,
                    ],
                    true
                ),
        };
    }
}