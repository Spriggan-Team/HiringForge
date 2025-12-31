<?php

namespace App\Application\Command\Handlers\Post;

use App\Api\DTO\Post\DeletePostRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Post\PostEraser;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class DeletePostCommandHandler
{

    public function __construct(private PostEraser $eraser,private ValidatorInterface $validator){}

    public function handle(DeletePostRequest $command): array
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