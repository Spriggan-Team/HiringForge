<?php

namespace App\Application\Query\Handlers\Account;

use Exception;

use App\Api\DTO\Account\GetAccountRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\Account\FetchAccount;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GetAccountQueryHandler {

    public function __construct(
        private FetchAccount $picker,
        private ValidatorInterface $validator
    ){}

    public function handle(GetAccountRequest $query): array
    {
        try{
            $error = $this->validator->validate($query);
            
            if(count($error) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $account = $this->picker->execute($query);
            return ApiResponseBuilder::success($account);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}