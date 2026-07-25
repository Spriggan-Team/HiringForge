<?php

namespace App\Application\Usecases\User;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;

class ViewPerformanceMetrics{
    public function __construct(
        private JobOfferQueryRepositoryInterace $query,
    ){}
    
    public function execute(string $userId){
        $result = $this->query->analyseJobOfferCollection(userId: $userId);
        return $result;
    }
}