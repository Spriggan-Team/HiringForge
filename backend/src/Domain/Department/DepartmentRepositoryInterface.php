<?php

namespace App\Domain\Department;


use App\Domain\Exception\ResourceNotFoundException;


interface DepartmentRepositoryInterface{
    /**
     * @throws \Exception | ResourceNotFoundException
     */
    public function  get(int $id): Department;

    public function findTreeByCompany(string $companyId, bool $onlyActive = false): array;

    public function save(Department $domain, bool $flush = true): Department;

    public function remove(Department $entity, bool $flush = true): void;

    public function exists(int $id): bool;

    /**
     * Return all associated departments for a specific company based on its id
     * 
     * @return list<array{
     *     id: int,
     *     name: string,
     *     parentId: ?int
     * }>
     */
    public function findDepartmentCollectionByCompanyId(string $companyId): array; 
}