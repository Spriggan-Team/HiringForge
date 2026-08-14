<?php

namespace App\Domain\Candidate;

use App\Domain\Exception\UnauthorizedAction;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\AccountRepositoryInterface;


/**
 * This interface describe how we can interact with the bdd
 */
interface CandidateRepositoryInterface 
{

    /**
     * Check if wether a candidate exist or not in the system
     */
    public function exists(string $candidateId) : bool;

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
     * Retreive meta data about an user's resumes
     * @throws \Exception this is thrown whenever something get wrong while exeuting the operation 
     * @throws RessourceNotFound no candidate found
     * @return array{0: string, 1: StaticMedia}  - returns an array media corresponding to the related cvs 
     */
    public function getResumeFiles(string $candidateId): array;


    /**
     * Verify association between user and resume file
     * & then fetch it if founded (null if not)
     */
    public function getResumeFileForCandidate(string $candidateId, string $fileId): ?StaticMedia;

    /**
     * Retreive a resume of a candidate
     */
    public function getResumeFile(string $fileId): ?StaticMedia;


    public function findResumeById( string $candidateId, string $resumeId ): ?StaticMedia;

    /**
     * retreive à light model of a connected user
     */
    public function getCandidateLightModel(string $candidateId): CandidateLightModel;

    /**
     * Verifies that the resume file belongs to the candidate.
     *
     * @throws UnauthorizedAction|AccessDeniedException if the resume does not belong to the candidate.
     */
    public function assertResumeBelongsToCandidate(
        string $candidateId,
        string $fileId
    ): void;


}