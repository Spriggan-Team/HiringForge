<?php

namespace App\Application\Command\Handlers\JobOffer;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\DTO\JobOffer\CreateJobOffer;

use App\Application\Command\Usecase\JobOffer\JobOfferRecorder;
use App\Application\DTO\RequireAuthentification;
use App\Infrastructure\Storage\FileStorage\FileUtils;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CreateJobOfferCommandHandler{

    public function __construct(
        private FileUtils $utils,
        private JobOfferRecorder $recorder,
        private ValidatorInterface $validator
    ){}

    public function handle(CreateJobOffer $command, RequireAuthentification $auth): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0)
                throw new BadRequestException("Bad fields validation");
            
            $this->recorder->execute(
                userId: $auth->actorId,
                title: $command->title,
                content: $command->content,
                image: $this->utils->parseAsStaticMedia($command->image),
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