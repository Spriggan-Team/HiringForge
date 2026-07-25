<?php


namespace App\Api\Controllers\Department\Mapper;

use App\Application\DTO\Department\CreateDepartmentRequest;

class CreateDepartmentRequestMapper
{
    public function fromArray(array $body): CreateDepartmentRequest{
        return new CreateDepartmentRequest(
            label: $body['label'],
            companyId: $body['companyId'],
            parentId: $body["parentId"],
            code: $body["code"],
            description: $body["description"],
            externalRef: $body["externalRef"],
        );
    }
}