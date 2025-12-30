<?php

namespace App\Application\Query\Handlers\Account;

use Exception;


use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\Account\FetchAccountCollection;


class GetAccountCollectionQueryHandler{

    public function __construct(private FetchAccountCollection $picker){}

    public function handle( $query): array
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