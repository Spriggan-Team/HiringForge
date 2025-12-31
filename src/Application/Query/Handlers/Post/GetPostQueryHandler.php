<?php

namespace App\Application\Query\Handlers\Post;

use Exception;

use App\Api\DTO\Post\GetPostRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\Post\PostReader;


use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class GetPostQueryHandler{

    public function __construct(private PostReader $reader, private ValidatorInterface $validator){}

    public function handle(GetPostRequest $query): array
    {
        try{
            $errors = $this->validator->validate($query);
            if(count($errors) > 0)
                throw new BadRequestHttpException("Bad fields validatrion");

            $postResponse = $this->reader->execute($query);
            
            return ApiResponseBuilder::success($postResponse);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}


?>