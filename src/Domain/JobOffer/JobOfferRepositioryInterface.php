<?php

namespace App\Domain\JobOffer;

use App\Domain\JobOffer\JobOffer;
use App\Domain\Shared\Account\AccountId;

interface JobOfferRepositioryInterface
{  
    /**
     * @return JobOffer
     * @throws RessourceNotFound
     * seachr for an existing JobOffer in the bdd an return it
     */
    public function findById(string $accountId, string $offerId): JobOffer;

    /**
     * WARNING: Use only when necessary
     * This one is used to apply heavy buisness roles to a list of collection of jobs as it returns a 
     * collection of domain object
     * @throws Exception
     * @return JobOffer[]
     */
    public function findAll(string $accountId, string $offerId): array;

    /**
     * This is used to change/modify a job offer in the bdd
     * @throws Exception
     */
    public function change(JobOffer $jobOffer, string $offerId, string $accountId): void;
    
    /**
     * @return void
     * save the JobOffer in bdd
     * adaptated for JobOffer, patch, put Htpp request
     */
    public function save(JobOffer $offer, AccountId $accountId): void;


    /**
     * @return void
     * delete a JobOffer using accountId and uuid
     */
    public function delete(string $uuid, string $accountId): void;


    /**
     * This function publish an offer
     * @param string $offerId the cif of the related offer
     * @param string $userId the id of the current user/actor
     * @return void 
     */
    public function publish(string $offerId, string $userId):void;
}