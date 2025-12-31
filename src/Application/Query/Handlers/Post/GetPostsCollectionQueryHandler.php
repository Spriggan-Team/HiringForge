<?php

namespace App\Application\Query\Handlers\Post;

use Exception;

use App\Api\DTO\Post\GetPostCollectiontRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\Post\PostCatalogReader;


use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetPostsCollectionQueryHandler {

  public function __construct(private PostCatalogReader $catalog, private ValidatorInterface $validator){}

  public function handle(GetPostCollectiontRequest $query): array
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