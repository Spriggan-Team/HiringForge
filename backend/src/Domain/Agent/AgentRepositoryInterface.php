<?php

namespace App\Domain\Agent;

interface AgentRepositoryInterface
{
    /**
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return Agent
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): Agent;


    /**
     * This function is meant to retreive an actor from the bdd uisng his email
     * @return Agent                the retriving actor (user, candidate, agent ...)
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     */
    public function findByEmail(string $email): Agent;

}