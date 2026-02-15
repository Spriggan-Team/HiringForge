<?php

namespace App\Application\Query\Handlers\JobOffer;

use Exception;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\JobOffer\JobOfferReader;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GetJobOfferQueryHandler
{

    public function __construct(private JobOfferReader $reader, private ValidatorInterface $validator){}

    public function handle(string $offerId)
    {
        try{
            if(!$offerId){
                return ApiResponseBuilder::error('Please, don\'t forget the id as a parameter in your request');
            }
            if(Uuid::isValid($offerId)){
                $offer = $this->reader->execute($offerId);
                return ApiResponseBuilder::success($offer);
            }
            return ApiResponseBuilder::error("Not such a job exist");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}


?>