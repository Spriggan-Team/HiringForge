<?php

namespace App\Domain\JobOffer;

use App\Domain\File\StaticMedia;
use App\Domain\JobOffer\JobOffer;
use App\Domain\Shared\Account\AccountId;

interface JobOfferRepositioryInterface
{  

    /**
     * Verifies that a job offer exists in the database and is linked to an existing user.
     *
     * @param string $offerId The identifier of the job offer to check.
     * @param string $userId  the identifier of the linked user 
     * @throws \DomainException|\Exception If the offer does not exist or the relation is invalid.
     */
    public function assertRelationWithUser(string $accountId, string $offerId): void;



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
     * This is used to change/modify a job offer entity in the bdd
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
     * @throws RessourceNotFound
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


    /**
     * Creates a relationship between a job offer and an image
     * in the database.
     * @param string                $offerId - The job offer to associate with the provided the image
     * @param JobOfferImage[]       $images  - The images to be associated
     * @throws \DomainException     Thrown when a business logic error occurs
     * @return void                     - when everything went smoothly
     */
    public function associateImagesWithJob(string $offerId, array $images): void;


    /**
     * Removes associated images of a job offer from the database.
     *
     * @param string $offerId   The unique identifier of the job offer.
     * @param string $fileNames   image - file names to remove.
     * @throws \DomainException     Thrown when a business logic error occurs
     */
    public function removeImageFromJob(string $offerId, string $fileName): void;

}