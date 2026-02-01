<?php


namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\Serializer\ActorView;
use App\Application\Command\Usecase\User\UserRegister;
use App\Application\DTO\User\RegisterUserCommand;
use App\Domain\Exception\EmailAlreadyRegistered;

use DomainException;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RegisterUserCommandHandler
{

    public function __construct(
        private ActorView $viewer,
        private UserRegister $register,
        private ValidatorInterface $validator
    ){}


    public function handle(RegisterUserCommand $command): array
    {
        try{
            $errors = $this->validator->validate($command);
            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }
            
            [$userId, $failed_upload] = $this->register->execute($command);

            return ApiResponseBuilder::success(
                $this->viewer->register($userId, $failed_upload),
                "Everything went suceesfully"
            );
        }
        catch(EmailAlreadyRegistered $alreadyExist){
            return ApiResponseBuilder::error("Is something wrong with your email ? ", $alreadyExist);
        }
        catch(BadRequestHttpException $exception)
        {
            return ApiResponseBuilder::error("Please check you data format and try again!", $exception);
        }
    }

}
