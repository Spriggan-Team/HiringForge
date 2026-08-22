<?php

namespace App\Application\Usecases\Department;

use App\Domain\Department\Department;
use App\Domain\Exception\RessourceNotFound;
use App\Application\DTO\Department\CreateDepartmentRequest;


use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;


class DepartmentGenerator{
    public function __construct(
       private CompanyRepositoryInterface $companyRepository,
       private DepartmentRepositoryInterface $departmentRepository,
    ){}
    
    /**
     * @throws RessourceNotFound
     */
    public function execute(
        CreateDepartmentRequest $command
    ): array{
        $companyExist = $this->companyRepository->exists($command->companyId);
        if(!$companyExist)
            throw new RessourceNotFound("Company not found");

        $department =  Department::create(
            label: $command->label,
            code: $command->code,
            companyId: $command->companyId,
            description: $command->description,
            externalRef: $command->externalRef,
        );


        // If it's a subdepartment (e.g., “Backend” under “Engineering”)
        if (!empty($command->parentId)) {
            try{
                $parent = $this->departmentRepository->get($command->parentId);
                if ($parent) {
                    $department->setParent($parent);
                }
            }catch(RessourceNotFound){}
        }

        $this->departmentRepository->save($department, true);
        return $department->toArray();
    }
}