<?php


namespace App\Application\Command\Handlers\User;


use App\Api\Responder\ApiResponse;

use App\Application\DTO\User\RegisterUserCommand;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Application\Usecases\User\UserRegisterUseCase;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RegisterUserCommandHandler
{

    public function __construct(
        private UserRegisterUseCase $register,
        private ValidatorInterface $validator
    ){}


    public function handle(
        RegisterUserCommand $command,
    ): ApiResponse
    {
        try{
            $errors = $this->validator->validate($command);
            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $registerData = $this->register->execute(
                name: $command->name,
                siret: $command->siret,
                email: $command->email,
                password: $command->password,
                address: $command->address,
                images: $command->images,
                videoPresentation: $command->videoPresentation
            );

            return ApiResponse::success(
                data: $registerData->failedUploading,
                message: "You've been registered"
            );
        }
        catch(EmailAlreadyRegistered $alreadyExist)
        {
            return ApiResponse::error("Is something wrong with your email ? ", throwable: $alreadyExist);
        }
        catch(BadRequestHttpException $exception)
        {
            return ApiResponse::error("Please check you data format and try again!", throwable: $exception);
        }
    }

}
