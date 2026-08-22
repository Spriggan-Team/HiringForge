<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\Application\Application;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;


class JobApplicationEntityMapper
{
    public static function toDoctrineEntity(
        Application $domain,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer
    ){

    }
}