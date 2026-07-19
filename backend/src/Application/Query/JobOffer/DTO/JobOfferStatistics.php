<?php

namespace App\Application\Query\JobOffer\DTO;

class JobOfferStatistics{
    
    public function __construct(
        public int $totalOffers,
        public int $viewCount,
        public int $applicationCount, //candidatures
        public int $activeOffers,
        public int $pendingReviewOffers,
        public int $closedOffers,
        public int $applicationRate,
        public int $scheduledInterviews,
    )
    {}
}