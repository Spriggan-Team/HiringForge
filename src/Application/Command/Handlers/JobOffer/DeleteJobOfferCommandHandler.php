<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;


use Ramsey\Uuid\Uuid;
use App\Api\Responder\ApiResponse;
use App\Application\Command\Usecase\JobOffer\JobOfferEraser;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteJobOfferCommandHandler
{

    public function __construct(
        private JobOfferEraser $eraser,
        private ValidatorInterface $validator
    ){}

    public function handle(string $offerId, ?string $accountId = null): ApiResponse
    {
        try{
            if(!Uuid::isValid($offerId) && Uuid::isValid($accountId)){
                throw new BadRequestHttpException();
            }

            $this->eraser->execute($offerId, $accountId);
            return ApiResponse::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}