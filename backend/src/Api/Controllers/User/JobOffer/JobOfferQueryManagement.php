<?php

namespace App\Api\Controllers\User\JobOffer;


use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Domain\JobOffer\JobPublicationStatus;


use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/users/job_offer')]
class JobOfferQueryManagement extends AbstractController{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferQueryRepositoryInterace $queryRepository,
    ){
        ApiResponse::init($logger);
    }

    #[Route('/', methods: ['GET'])]
    public function retreiveJobOfferWithPagination(
        Request $request
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $limit = $request->query->get("limit");
            $skip = $request->query->get("skip");
            $jobPublicationStatus = $request->query->get("publicationStatus") ?? JobPublicationStatus::PUBLISHED;
            
            $data = $this->queryRepository->fetchJobOfferViewCollection(
                userId: $user->getId(), limit: $limit, 
                skip: $skip, jobPublicationStatus: $jobPublicationStatus
            );
            return ApiResponse::success(
                        data: $data, message: "Everything went successfully"
                    )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                    "Something went wrong",
                    throwable: $exception
                )->toJsonResponse();
        }
    }



    #[Route('/{jobId}')]
    public function retreiveSingleJobOffer(
        string $jobId
    ){
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();
            $data = $this->queryRepository->fetchJobOfferViewById(
                userId: $user->getId(),
                offerId: $jobId
            );

            return ApiResponse::success(
                    data: $data,
                    message: "Everything went successfully"
                )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                    "Something went wrong",
                    throwable: $exception
            )->toJsonResponse();
        }
    }

    public function getViewAnalytics(){
        
    }

    
    #[Route('/kanban')]
    public function getKanbanResult(){

    }


}