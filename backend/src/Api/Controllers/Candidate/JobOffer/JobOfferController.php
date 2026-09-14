<?php

namespace App\Api\Controllers\Candidate\JobOffer;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\JobOffer\Repositories\CandidateJobOfferRepositoryInterface;
use App\Application\Query\JobOffer\Repositories\JobOfferAnalyticsRepositoryInterface;
use App\Application\Usecases\Candidate\ApplyToJobOffer;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;


use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/candidates/jobs')]
class JobOfferController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger
    )
    {
        ApiResponse::init($logger);
    }



    #[Route('/{offerId}/view', methods: ["POST"])]
    public function addView(
        string $offerId,
        CandidateJobOfferRepositoryInterface $queryRepository,
        JobOfferRepositoryInterface $repository
    ){
        try{
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();
            $this->logger->error("Candidate View maagement yeah yeah !!");
            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorize action",
                    statusCode: 401
                )->toJsonResponse();
            }
            $candidateId = $candidate->getId();

            $hasViewed = $queryRepository->hasViewed(
                candidateId: $candidateId,
                jobOfferId: $offerId
            );
            if(!$hasViewed){
                $repository->addView(
                    candidateId: $candidateId,
                    jobOfferId: $offerId 
                );
            }

            return ApiResponse::notice(
                message: "Everything is okay"
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while adding view ",
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }

}