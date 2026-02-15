<?php

namespace App\Application\Command\Handlers\User;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\ChangeEmail;
use App\Application\Command\Utils\AccountRepositoryFactory;
use App\Application\Usecases\account\AccountEmailRenitializer;
use App\Domain\Shared\Account\AccountRole;

use Exception;

class ChangeUserEmailCommandHandler
{
    public function __construct(
        private AccountEmailRenitializer $AccountEmailRenitializer,
        private AccountRepositoryFactory $accountRepositoryFactory
    )
    {}

    public function handle(ChangeEmail $command): ApiResponse
    {
        try
        {
            $repository = $this->accountRepositoryFactory->create(AccountRole::USER->value);

            $this->AccountEmailRenitializer->execute(
                oldEmail: $command->oldEMail,
                newEmail: $command->newEmail,
                password: $command->password,
                repository: $repository
            );

            return ApiResponse::notice('Your email has been updated');
        }
        catch(Exception $exceptions)
        {
            return ApiResponse::error('Your email has been updated', $exceptions);
        }
    }
}