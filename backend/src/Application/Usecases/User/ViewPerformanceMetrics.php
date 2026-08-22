<?php

namespace App\Application\Usecases\User;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;

class ViewPerformanceMetrics{
    public function __construct(
        private JobOfferQueryRepositoryInterface $query,
    ){}
    
    public function execute(string $userId){
        $result = $this->query->analyseJobOfferCollection(userId: $userId);
        return $result;
    }
}