<?php

namespace App\Api\Controllers\Language;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Domain\Shared\LanguageLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route("/languages")]
class LanguageController extends AbstractController
{
    public function __construct(
        LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }


    #[Route("", methods: ["GET"])]
    public function list(
        LanguageRepositoryInterface $repository
    ): JsonResponse{
        try{
            $result = [];
            $langagues = $repository->getAll();

            foreach($langagues as $language){
                $result[] = [
                    "id" => $language->id(),
                    "code" => $language->code(),
                    "label" => $language->label()
                ];
            }

            return ApiResponse::success(
                data: $result,
                statusCode: Response::HTTP_OK
            )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                statusCode: Response::HTTP_BAD_REQUEST
            )->toJsonResponse();
        }
    }


    #[Route("/level", methods:["GET"])]
    public function getLanguagesExperiencesLabel(){
        try{
            if(!$this->getUser()){
               return ApiResponse::error(
                    message: "Something went wrong",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }
            return ApiResponse::success(
                data: LanguageLevel::values(),
                statusCode: Response::HTTP_OK
            )->toJsonResponse();
        }
        catch(\Exception $error){
           return ApiResponse::error(
                message: "Something went wrong",
                statusCode: Response::HTTP_BAD_REQUEST
            )->toJsonResponse();
        }
    }


}
