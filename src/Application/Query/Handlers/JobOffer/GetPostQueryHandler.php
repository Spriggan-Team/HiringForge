<?php

namespace App\Application\Query\Handlers\JobOffer;

use Exception;

use App\Api\Responder\ApiResponseBuilder;
use App\Api\DTO\JobOffer\GetJobOfferRequest;
use App\Application\Query\Usecase\JobOffer\JobOfferReader;


use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class GetJobOfferQueryHandler
{

    public function __construct(private JobOfferReader $reader, private ValidatorInterface $validator){}

    public function handle(GetJobOfferRequest $query): array
    {
        try{
            $errors = $this->validator->validate($query);
            if(count($errors) > 0)
                throw new BadRequestHttpException("Bad fields validatrion");

            $offer = $this->reader->execute($query);
            
            return ApiResponseBuilder::success($offer);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}


?>