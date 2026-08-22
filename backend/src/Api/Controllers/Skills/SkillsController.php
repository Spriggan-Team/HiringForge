<?php

namespace App\Api\Controllers\Skills;

use App\Api\Responder\ApiResponse;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\Shared\Skill\SkillRepositoryInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/skills")]
class SkillsController extends AbstractController
{
    public function __construct(
        LoggerInterface $logger
    )
    {
        ApiResponse::init($logger);
    }

    #[Route("", methods: ["GET"])]
    public function search(
        Request $request,
        SkillRepositoryInterface $repository
    )
    {
        try{
            $text = $request->query->get("text", null);
            $locale = $request->query->get("locale", null);
            
            if(!$locale && $text){
                return ApiResponse::error(
                    message: "Please provide the \"text\"  & \"locale\" query parameters",
                    statusCode: 403
                )->toJsonResponse();
            }

            $result = $repository->fetchAssociativeArray(
                $text, 
                $locale,
                ["name", "id"]
            );

            return ApiResponse::success(
                data: $result,
                statusCode: 200
            )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                statusCode: 400,
                throwable: $exception
            )->toJsonResponse();
        }
    }


    #[Route("/expertises", methods: ["GET"])]
    public function getExpertiseCollection(){
        try{
            $result = JobOfferExpertise::values();
            return ApiResponse::success(
                data: $result,
                statusCode: 200
            )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                statusCode: 400,
                throwable: $exception
            )->toJsonResponse();
        }
    }

}