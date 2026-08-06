<?php

namespace App\Api\Controllers\User\JobOffer;


use App\Api\Responder\ApiResponse;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




#[Route('/users/job_offer')]
class JobOfferQueryManagement extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferQueryRepositoryInterace $queryRepository,
        private ApplicationRepositoryInterface $applicationRepository
    ) {
        ApiResponse::init($logger);
    }

    #[Route('/', methods: ['GET'])]
    public function retrieveJobOfferWithPagination(
        Request $request,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: "Unauthentificated action",
                    statusCode: 403
                )->toJsonResponse();
            }

            $limit = $request->query->has('limit') ? (int) $request->query->get('limit') : null;
            $skip  = $request->query->has('skip')  ? (int) $request->query->get('skip')  : null;

            // Extract all filters passed via the URL(publishedState[published]=true, offerState, salary, etc.)
            $criteria = $request->query->all();

            /** @var array<int, JobSummaryItem> $data */
            $data = $this->queryRepository->fetchJobOfferViewCollection(
                userId: $user->getId(),
                limit: $limit,
                skip: $skip,
                criteria: $criteria
            );

            return ApiResponse::success(
                data: $data,
                message: "Everything went successfully"
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            $logger->error('Error fetching job offers collection: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception,
                statusCode: 500
            )->toJsonResponse();
        }
    }


    #[Route('/count', methods: ['GET'])]
    public function countJobOffer(Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            // Extraire les critères transmis dans l'URL pour le comptage
            $criteria = $request->query->all();

            $count = $this->queryRepository->count($user->getId(), $criteria);

            return ApiResponse::success(
                data: $count,
                message: "Everything went successfully"
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception,
                statusCode: 500
            )->toJsonResponse();
        }
    }

    #[Route('/{jobId}', methods: ['GET'])]
    public function retrieveSingleJobOffer(string $jobId, Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Retreive local parameter
            $locale = $request->getLocale() ?: 'fr';

            // Projection schema tailored to the requirements of the JobView interface
            $scheme = [
                'id' => true,
                'title' => true,
                'content' => true,
                'jobWorkMode' => true,
                'mainImage' => true,
                'viewsCount' => true,
                'createdAt' => true,
                'updatedAt' => true,
                'publicationStatus' => true,
                'activityStatus' => true,
                'visibilityStatus' => true,
                'salary' => [
                    'devise' => true,
                    'min' => true,
                    'max' => true,
                ],
                'location' => [
                    'id' => true,
                    'street' => true,
                    'city' => true,
                    'country' => true,
                ],
                'department' => [
                    'id' => true,
                    'label' => true,
                ],
                'contract' => [
                    'id' => true,
                    'label' => true,
                ],
                'skills' => [
                    'locale' => $locale,
                    'id' => true,
                    'name' => true,
                    'isRequired' => true,
                ],
                'languages' => [
                    'label' => true,
                    'level' => true,
                ],
            ];

            $data = $this->queryRepository->fetchJobOfferProjection(
                jobOfferId: $jobId,
                userId: $user->getId(),
                scheme: $scheme
            );

            if (empty($data)) {
                return ApiResponse::error(
                    message: "Job offer not found",
                    statusCode: 404
                )->toJsonResponse();
            }


            return ApiResponse::success(
                data: $data,
                message: "Everything went successfully"
            )->toJsonResponse();

        }
        catch (\Throwable $exception) {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception
            )->toJsonResponse();
        }
    }




    /**
     * Route: /{jobId}/candidates/stats?timeframe=month|week
     */
    #[Route('/{jobId}/candidates/stats', methods: ['GET'])]
    public function getCandidateStats(
        string $jobId,
        Request $request
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //--  Sanitization and fallback to ‘month’ if the value is invalid
            $timeFrame = $request->query->get('timeframe', 'month');
            if (!in_array($timeFrame, ['month', 'week'], true)) {
                $timeFrame = 'month';
            }

            //-- Retrieving Application Metrics
            $candidateMetrics = $this->applicationRepository->getPostulationMetrics(
                userId: $user->getId(),
                jobId: $jobId,
                timeframe: $timeFrame
            );

            return ApiResponse::success(
                data: $candidateMetrics,
                statusCode: 200,
                message: 'Everything is fine'
            )->toJsonResponse();

        } catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while fetching candidate statistics',
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }
    

    #[Route('/{jobId}/postulation/metrics', methods: ['GET'])]
    public function getPostulationMetrics()
    {
        try{
            
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong',
                statusCode: 400
            )->toJsonResponse();
        }
    }


    #[Route('/kanban', methods: ['GET'])]
    public function getKanbanResult()
    {
    }
}