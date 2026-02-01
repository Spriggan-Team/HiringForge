<?php

namespace App\Application\Command\Handlers\Auth;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Auth\AgentAuthentificator;
use App\Application\DTO\AuthentificateActor;
use App\Application\Serializer\ActorView;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Shared\Actor\ActorRole;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AgentAuthCommandHandler
{
    public function __construct(
        private ActorView $viewer,
        private ValidatorInterface $validator,
        private AgentAuthentificator $authenticator
    )
    {}

    public function handle(AuthentificateActor $actor): array
    {
        try{
            $errors = $this->validator->validate($actor);
            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }
            
           $agentId = $this->authenticator->execute($actor);
           if($agentId){
                return ApiResponseBuilder::success(
                    $this->viewer->authentificate($agentId, ActorRole::AGENT),
                    "Everything went suceesfully"
                );
           }
           return ApiResponseBuilder::success(null, "Please check your credentials");
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