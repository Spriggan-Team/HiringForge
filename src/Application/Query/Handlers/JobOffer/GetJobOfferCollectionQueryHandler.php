<?php

namespace App\Application\Query\Handlers\JobOffer;

use Exception;


use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Application\Query\Usecase\JobOffer\JobOfferCatalogReader;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class GetJobOfferCollectionQueryHandler {

  public function __construct(private JobOfferCatalogReader $catalog, private ValidatorInterface $validator){}

  public function handle(GetJobOfferCollectiontRequest $query): array
  {
    try{
      $errors = $this->validator->validate($query);

      if(count($errors) > 0)
        throw new BadRequestException();
      
      $catalog = $this->catalog->execute($query);
      return ApiResponseBuilder::success($catalog, "Evrything went smootly");
    }
    catch(Exception $exception){
      throw $exception;
    }
  }
}


?>