<?php

namespace App\Domain\Repositories;

use App\Domain\Entity\Account;
use App\Domain\ValueObject\MergeRule;



interface AccountRepositoryInterface {
    /**
     * @return void
     * @throws Exception
     * use to create/update a new ressource in the bdd
    */
    public function save(Account $account, MergeRule $rule= MergeRule::FULL_OVERWRITE ): void;

    /**
     * @return Account
     * return the specified account requested if founded in the bdd storage
     */
    public function getById(int $uuid): Account;


    /**
     * @return Account[]
     * return an array of all the accounts existing in the bdd
     */
    public function getAll(): array;

    public function delete(string $uuid): void;

}