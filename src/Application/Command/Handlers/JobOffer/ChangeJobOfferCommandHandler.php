<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Application\Command\Usecase\JobOffer\JobOfferModifier;


use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class ChangeJobOfferCommandHandler
{
    public function __construct(
        private JobOfferModifier $modifier,
        private ValidatorInterface $validator,
    ){}

    public function handle(ChangeJobOffferRequest $command, string $accountId): ApiResponse
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestException("Bad Fields Validation");
            }
            
            $this->modifier->execute(
                userId: $accountId,
                jobOfferId: $command->uuid,
                title: $command->title,
                content: $command->content,
            );

            return ApiResponse::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            return ApiResponse::error("Something went wrong please, check your data and try again");;
        }
    }
}