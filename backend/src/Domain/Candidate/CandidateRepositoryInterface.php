<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\AccountRepositoryInterface;

/**
 * This interface describe how we can interact with the bdd
 */
interface CandidateRepositoryInterface 
{

    /**
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return Candidate
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): Candidate;


    
    /**
     * This function is meant to retreive an actor from the bdd uisng his email
     * @return Candidate                the retriving actor (user, candidate, agent ...)
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     */
    public function findByEmail(string $email): Candidate;

    /**
     * This function is used to persist a  new candidate in the bdd
     * @param Candidate $candidate The candidate you want to register
     * @throws Exception
     */
    public function save(Candidate $candidate): void;


    /**
     * This a function that must be used for candidate'applications (postulations)
     * @throws DomainException|Exception It is thrown when no actual job offer or candidate extists 
    */
    public function apply(string $candidateId, string $offerId): void;

    
    /**
     * Retreive meta data about an user's cv
     * @throws \Exception this is thrown wheenever something get wrong while exeuting the operation 
     * @return StaticMedia | null - returns a stactic media corresponding to the related cv but if something went wrong
     *                              for some reason without throwing an exception (no critical) 
     *                              then what is retruned will be null
     */
    public function getCVFile(string $candidate): StaticMedia | null;
}