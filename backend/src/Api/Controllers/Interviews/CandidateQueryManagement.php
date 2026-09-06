<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CandidateQueryManagement extends AbstractController
{
    public function __construct(){}

    #[Route('/')]
    public function getInterviewsAgenda(){
        try{
            $candidate = $this->getUser();
            $results = [];
            return ApiResponse::success(
                data: $results,
                message: "Everything is okay"
            );
        }
        catch(\Throwable $throwable){
            return ApiResponse::error(
                message: "Something went wrong while retreiving interview for candidate",
                statusCode: Response::HTTP_BAD_REQUEST,
                throwable: $throwable
            )->toJsonResponse();
        }
    }
}