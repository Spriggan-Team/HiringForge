<?php

namespace App\Domain\Company;


interface CompanyRepositoryInterface{

    /** 
     * Save a company if already not registered
     * @throws ResourceCreationRejected
     */
    public function save(Company $comany): void;

    /**
     * Check if an company already exists in bdd
     * @return bool true if company already regustered
     */
    public function exists(string $companyName): bool;
}