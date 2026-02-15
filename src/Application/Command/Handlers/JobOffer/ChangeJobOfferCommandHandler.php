<?php

namespace App\Application\Command\Handlers\JobOffer;


use Exception;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Application\Command\Usecase\JobOffer\JobOfferModifier;
use App\Application\DTO\RequireAuthentification;
use App\Infrastructure\Storage\FileStorage\FileUtils;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class ChangeJobOfferCommandHandler
{
    public function __construct(
        private JobOfferModifier $modifier,
        private FileUtils $fileUtils,
        private ValidatorInterface $validator,
    ){}

    public function handle(ChangeJobOffferRequest $command, RequireAuthentification $auth): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestException("Bad Fields Validation");
            }
            
            $this->modifier->execute(
                userId: $auth->actorId,
                jobOfferId: $command->uuid,
                title: $command->title,
                content: $command->content,
                image: $this->fileUtils->parseAsStaticMedia($command->image),
            );

            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            return ApiResponseBuilder::error("Something went wrong please, check your data and try again");;
        }
    }
}