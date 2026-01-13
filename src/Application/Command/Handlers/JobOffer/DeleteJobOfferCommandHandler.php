<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\JobOffer\JobOfferEraser;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteJobOfferCommandHandler
{

    public function __construct(private JobOfferEraser $eraser,private ValidatorInterface $validator){}

    public function handle(DeleteJobOfferRequest $command): array
    {
        try{
            $errors = $this->validator->validate($command);
            
            if(count($errors) > 0){
                throw new BadRequestHttpException();
            }

            $this->eraser->execute($command);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}