<?php

namespace App\Application\Query\JobOffer\DTO;

final class JobOfferStatistics
{
    public function __construct(
        public readonly int $totalOffers,
        public readonly int $viewCount,
        public readonly int $applicationCount,
        public readonly int $activeOffers,
        public readonly int $pendingReviewOffers,
        public readonly int $publishedOffers,
        public readonly int $draftOffers,
        public readonly int $closedOffers,
        public readonly int $publicOffers,
        public readonly int $privateOffers,
        public readonly float $applicationRate,
        public readonly int $hiredApplicationCount,
        public readonly int $scheduledInterviews,
        public readonly int $totalGeneratedEmploymentOffers,
        public readonly int $totalAcceptedEmploymentOffers,
        public readonly float $applicationIncreaseThisWeek,
        public readonly float $generatedOfferEmploymentIncreaseThisWeek,
        public readonly float $acceptedOfferEmploymentIncreaseThisWeek,
        public readonly float $interviewsIncreaseThisWeek,
        public readonly float $hiredIncreaseThisWeek,
    ) {}
}