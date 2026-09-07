<?php

namespace App\Application\Usecases\Agent\Assignement;

use App\Application\DTO\Agent\CreateAssignementCommand;

class CreateAssignementUsecase
{
    /**
     * This function helps create a task/assignement and assign to the
     * and incidentally assign then to some designatd agent (if provided)
     * @return string The new id that has been created and associated to the the assignement
     */
    public function execute(CreateAssignementCommand $command): string
    {
        return "";
    }
}