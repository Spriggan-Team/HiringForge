<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;


use Ramsey\Uuid\Uuid;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\JobOffer\JobOfferEraser;
use App\Application\DTO\RequireAuthentification;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteJobOfferCommandHandler
{

    public function __construct(
        private JobOfferEraser $eraser,
        private ValidatorInterface $validator
    ){}

    public function handle(string $offerId, RequireAuthentification $auth): array
    {
        try{
            if(!Uuid::isValid($offerId) && Uuid::isValid($auth->actorId)){
                throw new BadRequestHttpException();
            }

            $this->eraser->execute($offerId, $auth->actorId);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}