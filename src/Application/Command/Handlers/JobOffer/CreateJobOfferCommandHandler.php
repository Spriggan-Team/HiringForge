<?php

namespace App\Application\Command\Handlers\JobOffer;

use App\Api\Responder\ApiResponse;
use Exception;



use App\Application\DTO\JobOffer\CreateJobOffer;

use App\Application\Command\Usecase\JobOffer\JobOfferRecorder;


use App\Infrastructure\Storage\FileStorage\FileUtils;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class CreateJobOfferCommandHandler{

    public function __construct(
        private JobOfferRecorder $recorder,
        private ValidatorInterface $validator
    ){}

    public function handle(CreateJobOffer $command, string $accountId, array $roles): ApiResponse
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0)
                throw new BadRequestException("Bad fields validation");
            
            $this->recorder->execute(
                accountId: $accountId,
                title: $command->title,
                content: $command->content,
                categories: $command->categories
            );
            return ApiResponse::notice("Everything went smoothly", 201);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }
}