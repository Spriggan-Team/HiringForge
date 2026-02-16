<?php

namespace App\Application\Query\Handlers\JobOffer;

use Exception;

use App\Api\Responder\ApiResponse;
use App\Application\Query\Usecase\JobOffer\JobOfferReader;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GetJobOfferQueryHandler
{

    public function __construct(private JobOfferReader $reader, private ValidatorInterface $validator){}

    public function handle(string $offerId): ApiResponse
    {
        try{
            if(!$offerId){
                return ApiResponse::error('Please, don\'t forget the id as a parameter in your request');
            }
            if(Uuid::isValid($offerId)){
                $offer = $this->reader->execute($offerId);
                return ApiResponse::success($offer);
            }
            return ApiResponse::error("Not such a job exist");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}


?>