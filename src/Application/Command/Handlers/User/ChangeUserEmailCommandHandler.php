<?php

namespace App\Application\Command\Handlers\User;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\User\UserResetEmail;
use App\Application\DTO\ChangeEmail;
use Exception;

class ChangeUserEmailCommandHandler
{
    public function __construct(
        private UserResetEmail $userResetEmail
    )
    {}

    public function handle(ChangeEmail $command): array
    {
        try
        {
            $response =  $this->userResetEmail->execute(
                oldEmail: $command->oldEMail,
                newEmail: $command->newEmail,
                password: $command->password
            );
            return ApiResponseBuilder::success($response, 'Your email has been updated');
        }
        catch(Exception $exceptions)
        {
            return ApiResponseBuilder::error('Your email has been updated', $exceptions);
        }
    }
}