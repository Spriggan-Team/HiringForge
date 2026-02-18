<?php

namespace App\Application\Command\Handlers\Account;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\ChangeEmail;
use App\Application\Usecases\account\AccountEmailRenitializer;

use Exception;

class ChangeAccountEmailCommandHandler
{
    public function __construct(
        private AccountEmailRenitializer $AccountEmailRenitializer,
    )
    {}

    public function handle(ChangeEmail $command): ApiResponse
    {
        try
        {

            $this->AccountEmailRenitializer->execute(
                oldEmail: $command->oldEMail,
                newEmail: $command->newEmail,
                password: $command->password,
                verificationToken: $command->verificationToken
            );

            return ApiResponse::notice('Your email has been updated');
        }
        catch(\DomainException $domainException)
        {
            //contains logic domain message (something that coulb be return to the client);
            return ApiResponse::error($domainException->getMessage()); 
        }
        catch(Exception $exceptions)
        {
            return ApiResponse::error('Something wrong happened', $exceptions);
        }
    }
}