<?php

namespace App\Application\DTO\Department;

use Symfony\Component\Validator\Constraints as Assert;


class CreateDepartmentRequest{
    public function __construct(
        #[Assert\NotBlank]
        /**  label of depament */
        public string $label,
         

        #[Assert\NotBlank]
        /** the associated organization */
        public string $companyId,


        /** Parent id (id asociated) */
        public ?int $parentId = null,

        /** code of department */
        public ?string $code = null,

        /** description */
        public ?string $description =  null,

        public ?string $externalRef= null,

    ){}
}
