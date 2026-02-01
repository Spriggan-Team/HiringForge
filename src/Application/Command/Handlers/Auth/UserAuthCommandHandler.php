<?php

namespace App\Application\Command\Handlers\Auth;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Serializer\ActorView;
use App\Application\DTO\AuthentificateActor;
use App\Application\Command\Usecase\Auth\UserAuthenticator;

use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Actor\ActorRole;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserAuthCommandHandler
{
    public function __construct(
        private ActorView $viewer,
        private ValidatorInterface $validator,
        private UserAuthenticator $authentificator
    ){}

    public function handle(AuthentificateActor $actor): array
    {
        try{
            $errors = $this->validator->validate($actor);
            if(count($errors)> 0){
                throw new BadRequestHttpException("bad field validartion");
            }
            
            $userId = $this->authentificator->execute($actor);
            if($userId){
                return ApiResponseBuilder::success(
                    data: $this->viewer->authentificate($userId, ActorRole::USER),
                    message: "Everything went smootly"
                );
            }
            return ApiResponseBuilder::success(
                data: null,
                message:"Please check your credentials"
            );
        }
        catch(BadRequestHttpException $badException)
        {
            return ApiResponseBuilder::error("Please chech you request data", $badException);
        }
        catch(RessourceNotFound $notFound)
        {
            return ApiResponseBuilder::error("Your are not elligible to this service", $notFound);
        }
    }
}