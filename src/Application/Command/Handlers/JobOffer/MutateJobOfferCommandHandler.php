<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;

use App\Api\DTO\JobOffer\MutateJobOfferRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\JobOffer\JobOfferModifier;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class MutateJobOfferCommandHandler
{
    public function __construct(private JobOfferModifier $modifier, private ValidatorInterface $validator){}

    public function handle(MutateJobOfferRequest $command): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestException("Bad Fields Validation");
            }
            
            $this->modifier->execute($command);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}