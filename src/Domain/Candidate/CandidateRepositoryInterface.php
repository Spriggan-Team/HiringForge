<?php

namespace App\Domain\Candidate;

use App\Domain\Shared\Actor\ActorRepositoryInterface;

/**
 * This interface describe how we can interact with the bdd
 */
interface CandidateRepositoryInterface extends ActorRepositoryInterface
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
}