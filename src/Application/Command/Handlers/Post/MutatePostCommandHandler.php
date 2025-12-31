<?php

namespace App\Application\Command\Handlers\Post;

use Exception;

use App\Api\DTO\Post\MutatePostRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Post\PostModifier;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class MutatePostCommandHandler
{
    public function __construct(private PostModifier $modifier, private ValidatorInterface $validator){}

    public function handle(MutatePostRequest $command): array
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