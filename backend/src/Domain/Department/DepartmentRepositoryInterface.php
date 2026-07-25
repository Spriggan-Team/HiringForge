<?php

namespace App\Domain\Department;


use App\Domain\Exception\RessourceNotFound;


interface DepartmentRepositoryInterface{
    /**
     * @throws \Exception | RessourceNotFound
     */
    public function  get(int $id): Department;

    public function findTreeByCompany(string $companyId, bool $onlyActive = false): array;


    public function save(Department $domain, bool $flush = true): void;

    public function remove(Department $entity, bool $flush = true): void;
}