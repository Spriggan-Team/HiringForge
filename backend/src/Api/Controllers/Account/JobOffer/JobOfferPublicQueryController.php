<?php

namespace App\Api\Controllers\Account\JobOffer;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Application\Usecases\Account\JobOffer\JobOfferCatalogReader;
use App\Application\Usecases\Account\JobOffer\PublicJobOfferReader;

use Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/jobs")]
class JobOfferPublicQueryController extends AbstractController
{

    #[Route('/', methods: ["GET"], name: "fetch_all_job_offer")]
    public function fetchAllJobOffer(
        Request $request,
        JobOfferCatalogReader $usecase
    ): JsonResponse
    {
        try
        {
            $query = new GetJobOfferCollectiontRequest(
                skip: $request->query->get('skip'),
                limit: $request->query->get('limit')
            );

            $catalog = $usecase->execute($query);
            return ApiResponse::success(
                data: $catalog,
                message: "Everything went smoothly"
            )->toJsonResponse();
        }
        catch(Exception $ex)
        {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $ex,
                statusCode:404
            )->toJsonResponse();
        }
    }



    #[Route("/{offerId}", methods: ["GET"], name: "fetch_one_job_offer")]
    public function fetchOneJobOffer(
        string $offerId,
        PublicJobOfferReader $reader,
    ): JsonResponse
    {
        try
        {
            if(!$offerId){
                return ApiResponse::error(message: 'Please, don\'t forget the id as a parameter in your request')->toJsonResponse();
            }
            $readable = $reader->execute($offerId);
            return ApiResponse::success(data: $readable)->toJsonResponse();
        }
        catch(Exception $ex){
            return  ApiResponse::error(
                message: "Something went wrong",
                throwable: $ex
            )->toJsonResponse();
        }
    }

}