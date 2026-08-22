<?php

namespace App\Domain\Shared\Contract;


interface ContractTypeRepositoryInterface{
    public function get(string $id): ContractType;

    public function getAll(string $country,  ?string $organizationId): array;

    public function exists(int $id): bool;
}