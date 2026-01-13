<?php

namespace App\Domain\Repositories;

use App\Domain\Entity\JobOffer;
use App\Domain\ValueObject\MergeRule;

interface JobOfferRepositioryInterface
{
    /**
     * @return array<JobOffer>
     * return a collection of all the JobOffer stored in bdd
     */
    public function getAll(string $accountId): array;
    
    /**
     * @return JobOffer
     * @throws ApiRessourceNotFound
     * seachr for an existing JobOffer in the bdd an return it
     */
    public function getById(string $id, string $offerId): ?JobOffer;
    
    /**
     * @return void
     * save the JobOffer in bdd
     * adaptated for JobOffer, patch, put Htpp request
     */
    public function save(JobOffer $offer, string $accountId,  MergeRule $rule = MergeRule::FULL_OVERWRITE ): void;


    public function delete(string $accountId, string $uuid ): void;
}