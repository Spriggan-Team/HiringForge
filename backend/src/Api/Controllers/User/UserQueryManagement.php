<?php

namespace App\Api\Controllers\User;


use App\Api\Responder\ApiResponse;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Application\Usecases\User\FetchUser;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/users")]
class UserQueryManagement extends AbstractController
{
    public function __construct(
    private LoggerInterface $logger,
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponse::init($logger);
    }

    #[Route('/basic-info', methods: ['GET'])]
    public function getBasicInfo(){}


    /**
     * This one allow you to get a  users' information with the appropriate persmission
     */
    #[Route("/profile", methods: ["GET"], name: "fetch_user")]
    public function getUserById(
        Request $request,
        FetchUser $handler
    ): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $response = $handler->execute($user->getId());
            return ApiResponse::success($response, "Everything went smoothly")->toJsonResponse();
        }
        catch (\Exception $exception)
        {
            return ApiResponse::error('User not found', throwable: $exception)->toJsonResponse();
        }
    }

    
    #[Route("/kpi", methods: ['GET'], name: "view_kpi_metrics")]
    public function getKpi(
        JobOfferQueryRepositoryInterace $jobOfferQueryRepository
    ) {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $result = $jobOfferQueryRepository->analyseJobOfferCollection($user->getId());
            return ApiResponse::success(data: $result, message: "Everything went successfully")->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something wrong happened",
                throwable: $exception
            )->toJsonResponse();
        }
    }

    #[Route('/kanban')]
    public function getKanbanResult(){

    }
}