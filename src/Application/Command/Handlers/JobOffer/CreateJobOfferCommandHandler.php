<?php

namespace App\Application\Command\Handlers\JobOffer;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\DTO\JobOffer\CreateJobOffer;

use App\Application\Command\Usecase\JobOffer\JobOfferRecorder;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CreateJobOfferCommandHandler{

    public function __construct(private JobOfferRecorder $recorder, private ValidatorInterface $validator){}

    public function handle(CreateJobOffer $command, string $userId): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0)
                throw new BadRequestException("Bad fields validation");
            
            $this->recorder->execute(
                userId: $userId,
                title: $command->title,
                content: $command->content,
                categories: $command->categories
            );
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }
}