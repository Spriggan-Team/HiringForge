<?php

namespace App\Application\Query\JobOffer;

interface JobOfferQueryRepositoryInterace
{
    /**
     * This return a view of a offer in the bdd.
     *  @throws RessourceNotFound
     *  @return JobOffertListItem
     */
    public function fetchJobOfferViewById(string $offerId): JobOffertListItem;

    /**
     * This function return a collection of all the JobOffer stored in bdd; The collection return must
     * @param string $accountId             The id of an user
     * @param ?int $limit                   The number of items you want to get back
     * @param ?int skip                     The number of element you want to ignore in desc order by creation date
     * @return JobOffertListItem[]          #should return a serializable value;
     */
    public function fetchJobOfferViewCollection(?int $limit= null, ?int $skip=null): array;
}