<?php

namespace App\Domain\Agent;

use App\Domain\Shared\KnownIdentity;

interface AgentRepositoryInterface
{
    /**
     * This function search for an agent by its id throughout the database &
     * then returned the response 
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return Agent
     * @throws ResourceNotFoundException    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): Agent;


    /**
     * This one is used to verify  an agent existence
     * (or registration)
     * @param ?string $uuid - the id of the agent to look for
     * @param ?string $email - the associated email (if available)
     * @throws ResourceNotFoundException
     */
    public function exists(?string $uuid=null, ?string $email =null): KnownIdentity;


    /**
     * This function is meant to retreive an actor from the bdd uisng his email
     * @return Agent                the retriving actor (user, candidate, agent ...)
     * @throws ResourceNotFoundException    this exception should be throw when the ressouce does not exist in bdd
     */
    public function findByEmail(string $email): Agent;

    /**
     * This function is used for save or registered a new 
     * agent into the datasource (bdd)
     * @param Agent - the new account that has to be created in the database
     * 
     */
    public function save(Agent $agent): void;


    /**
     * This function verify/check if an account is indeed
     * author of the related agent
     * @throws \DomainException - is thrown when ownership verification failed
     */
    public function verifyOwnerShip(string $authorId,string $agentId): void;
    

    /**
     * This helps delete information about an agent in the bdd
     */
    public function delete(string $id): void;
}