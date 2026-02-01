<?php

namespace App\Application\Command\Handlers\Auth;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Auth\CandidateAuthentificator;

use App\Application\DTO\AuthentificateActor;
use App\Domain\Exception\RessourceNotFound;

use App\Application\Serializer\ActorView;
use App\Domain\Shared\Actor\ActorRole;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CandidateAuthCommandHandler
{
    public function __construct(
        private ActorView $viewer,
        private ValidatorInterface $validator,
        private CandidateAuthentificator $authentificator,
    )
    {} 

    public function handle(AuthentificateActor $actor): array
    {
        try{
            $errors = $this->validator->validate($actor);
            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad field validation");
            }
            
            $candidateId = $this->authentificator->execute($actor);
            if($candidateId){
                return ApiResponseBuilder::success(
                    $this->viewer->authentificate($candidateId, ActorRole::CANDIDATE),
                    "Everything went smoothly"
                );
            }
            return ApiResponseBuilder::success(null, "Please check your credentials");
        }
        catch(BadRequestHttpException $badRequest)
        {
            return ApiResponseBuilder::error("Please check your  request data", $badRequest);
        }
        catch(RessourceNotFound $notFound){
            return ApiResponseBuilder::error("Your are not elligible to this service", $notFound);
        }
    }
}