<?php

namespace App\Application\Query\Handlers\User;

use Exception;


use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\User\GetUserCollectionRequest;
use App\Application\Query\Usecase\User\FetchUserCollection;


class GetUserCollectionQueryHandler{

    public function __construct(private FetchUserCollection $picker){}

    public function handle(GetUserCollectionRequest $query): array
    {
        try{
            $collection = $this->picker->execute($query);
            return ApiResponseBuilder::success($collection, "Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    } 

}