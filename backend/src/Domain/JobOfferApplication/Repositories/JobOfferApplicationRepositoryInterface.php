<?php

namespace App\Domain\JobOfferApplication\Repositories;


interface JobOfferApplicationRepositoryInterface
{
    /**
     * Checks whether a job application exists in the system.
     *
     * @param string $id The job application identifier
     * @throws Exception|DomainException is thrown when nothing is found
     */
    public function assertExists(string $id): void;


}