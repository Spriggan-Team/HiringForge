<?php

namespace App\Application\Query\Handlers\User;

use Exception;

use App\Api\DTO\User\GetUserRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\User\FetchUser;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class GetUserQueryHandler
{

    public function __construct(
        private FetchUser $picker,
        private ValidatorInterface $validator
    ){}

    public function handle(GetUserRequest $query): array
    {
        try{
            $error = $this->validator->validate($query);
            
            if(count($error) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $user = $this->picker->execute($query);
            return ApiResponseBuilder::success($user);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}