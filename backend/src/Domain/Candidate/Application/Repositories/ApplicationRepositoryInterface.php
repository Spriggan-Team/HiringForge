<?php

namespace App\Domain\Candidate\Application\Repositories;

interface ApplicationRepositoryInterface
{
    /**
     * Checks whether a job application exists in the system.
     *
     * @param string $id The job application identifier
     * @throws Exception|DomainException is thrown when nothing is found
     */
    public function assertExists(string $id): void;


    /** 
     * count all related application of an user to a job
     * @param array $criteria
     *          ex: [
     *              'companyId' => string,
     *              'jobOfferId' => string,
     *              'status'? => JobApplicationStatus
     *          ]
    */
    public function count(array $criteria): int;

}