<?php

namespace App\Domain\JobOffer;

use App\Domain\User\UserId;
use App\Domain\JobOffer\JobOffer;



interface JobOfferRepositioryInterface
{
    /**
     * This return a view of a offer in the bdd.
     *  @throws RessourceNotFound
     *  @return JobOffertListItem
     */
    public function findById(string $offerId): JobOffertListItem;

    /**
     * @param string $accountId             The id of an user
     * @param ?int $limit                   The number of items you want to get back
     * @param ?int skip                     The number of element you want to ignore in desc order by creation date
     * @return JobOffertListItem[]          #should return a serializable value;
     * return a collection of all the JobOffer stored in bdd
     */
    public function getAll(?int $limit= null, ?int $skip=null): array;
    
    /**
     * @return JobOffer
     * @throws RessourceNotFound
     * seachr for an existing JobOffer in the bdd an return it
     */
    public function getById(string $id, string $offerId): JobOffer;

    /**
     * This is used to change/modify a job offer in the bdd
     * @throws Exception
     */
    public function change(JobOffer $jobOffer, string $offerId, string $userId): void;
    
    /**
     * @return void
     * save the JobOffer in bdd
     * adaptated for JobOffer, patch, put Htpp request
     */
    public function save(JobOffer $offer, UserId $userId): void;


    /**
     * @return void
     * delete a JobOffer using accountId and uuid
     */
    public function delete(string $uuid, string $userId): void;


    /**
     * This function publish an offer
     * @param string $offerId the cif of the related offer
     * @param string $userId the id of the current user/actor
     * @return void 
     */
    public function publish(string $offerId, string $userId):void;
}