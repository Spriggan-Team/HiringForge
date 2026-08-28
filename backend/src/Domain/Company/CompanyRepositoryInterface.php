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


    /**
     * @throws RessourceNotFound
     */
    public function get(string $companyId): Company;


    /**
     * verify wether an user is related to a company or noot
     * @return bool
     */
    public function containsUser(string $userId, string $companyId): bool;


    /**
     * Check if an addressed is link to a company
     * through the user
     */
    public function isAddressOwnedByUserCompany(
        string $addressId,
        string $userId
    ): bool;


    /**
     * @param array $scheme returned data porjection
     *                      ex: [
     *                              'id'? => bool //Company adderess
     *                              'name'? => bool
     *                              'siret'? => bool
     *                              'address[id]'?=> bool
     *                              'address[city]'? => bool
     *                              'address[country]'? => bool
     *                              'address[postalCode]'? => bool
     *                              'address[address][limit:1]'? => bool (ex : ici limit le result qu'au premier)
     *                          ]
     * @return array<string, mixed>|null
     *  example of returned value
     *                 [
     *                     'name' => 'Tech Solutions SAS',
     *                     'address' => [
     *                         'city' => 'Paris',
     *                         'postalCode' => '75008',
     *                         'country' => 'France',
     *                      ]
     *                  ]
     */
    public function fetchUserCompanyProjection(string $userId, array $scheme = ["id" => true]): array | null;

}