<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Api\Controllers\Helpers\ApiControllerHelpers;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Interviews\InterviewStatus;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Route('/interviews/users')]
class InterviewsUserQueryManagement extends AbstractController{

    use ApiControllerHelpers;

    public function __construct(
        LoggerInterface $logger,
        private PathResolverInterface $pathResolver,
        private InterviewsRepositoryInterface $interviewsRepository,
    )
    {
        ApiResponse::init($logger);
    }


    /**
     * Retreive a candidate image after checking its 
     * link with a recruiter
     *  - Queries
     *      - candidateId: string
     *      - interviewId: string
     */
    #[Route('/candidate/image')]
    public function getCandidateImage(
        Request $request,
        InterviewsRepositoryInterface $applicationRepository,
        AccountRepositoryInterface $accountRespository
    )
    {
        try{
           /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $candidateId = $request->query->get("candidateId");
            $interviewId = $request->query->get("interviewId");

            if(!$candidateId || !$interviewId){
                return ApiResponse::error(
                    message: 'candidateId & interviewId queries are mandatories',
                    statusCode: 400
                )->toJsonResponse();
            }

            //-- Check if requriements
            $applicationRepository->assertRecruiterHasAccessToInterview(
                recruiterId: $user->getId(),
                candidateId: $candidateId,
                interviewId: $interviewId
            );

            $media = $accountRespository->getProfileImage($candidateId);
            
            if ($media === null) {
                return ApiResponse::error(
                    message: 'No profile image found for this user.',
                    statusCode: 404
                )->toJsonResponse();
            }
            
            $fullPath = $this->pathResolver->resolveStoragePath(
                params: AccountStorageParams::candidateProfileImage(
                    candidateId: $candidateId,
                    storedFileName: $media->name
                ),
                mimeType: $media->mime
            );

            return new BinaryFileResponse($fullPath);
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving using intervirviews as base",
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Generate interviews
     * Route: "/interviews/users/agenda"
     * Queries:
     *  - date: ISO String
     *  - skip: number
     *  - limit: number
     */
    #[Route('/agenda', methods: ['GET'])]
    public function getRecentByDate(
        Request $request
    ): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            
            //-- Date
            $rawDate = $request->query->get('date');
            $date = $rawDate !== null 
                ? new \DateTimeImmutable($rawDate) 
                : new \DateTimeImmutable();

            // Paganition params
            $skip = $request->query->getInt('skip', 0);
            $limit = $request->query->getInt('limit', 10);

            $results = $this->interviewsRepository->getInterviewAgenda(
                userId: $user->getId(),
                skip: $skip,
                limit: $limit,
                date: $date
            );

            return ApiResponse::success(
                data: $results
            )->toJsonResponse();
        }
        catch (\Exception $error) {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Route /users/job_offer/?limit=number&skip=number
     * Queries:
     *  - limit?: number
     *  - skip?:  number
     *  - jobId?: number
     *  - companyId?: number
     *  - statuses?: InterviewsStatus
     */
    #[Route('/job_offers', methods: ['GET'])]
    public function getUserInterviewsWithPagination(
        Request $request
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Ids params
            $jobId = $request->query->get("jobId");
            $companyId = $request->query->get("companyId");
            
            //-- Custom filters
            $statuses = null;
            $statusesRaw = $request->query->get('statuses');
            if ($statusesRaw) {
                $decoded = json_decode($statusesRaw, true);
                if (is_array($decoded)) {
                    $statuses = array_map(
                        static fn(string $status) => InterviewStatus::from($status), 
                        $decoded
                    );
                }
            }

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);

            //-- Fetching interviews with projection
            $results = $this->interviewsRepository->fetchJobInterviewsProjection(
                jobId: $jobId,
                limit: $limit,
                skip: $skip,
                companyId: $companyId, 
                statuses: $statuses,
                scheme: [
                    'id' => true,
                    'startDate' => true,
                    'minutes' => true,
                    'status' => true,
                    'description' => true,
                    'candidateApproval' => true,
                    'candidate' => [
                        'id' => true,
                        'firstName' => true,
                        'lastName' => true,
                        'email' => true,
                        'image' => [
                            'name' => true,
                            'mime' => true,
                        ],
                    ],
                ],
                userId: $user->getId()
            );

            return ApiResponse::success(
                data: $results,
                statusCode: 200
            )->toJsonResponse();

        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: "Something went wrong while fetching interviews",
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Retreive all interview existing within a month
     * Queries:
     *      - currentMonth: string ISO
     */
    #[Route('/calendar', methods: ['GET'])]
    public function getUserInterviewCalendar(
        Request $request
    ){
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            $currentMonthParam = $request->query->get("currentMonth");

            if(!$currentMonthParam){
                return ApiResponse::error(
                    message: "\'currentMonth\' query is mandatory"
                )->toJsonResponse();
            }

            $currentMonth = new \DateTimeImmutable($currentMonthParam);
            $results = $this->interviewsRepository->getCalendarCollectionViews(userId: $user->getId(), month: $currentMonth) ?? [];

            return ApiResponse::success(
                message: "Everyhing is ok",
                data: $results
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving interviws calendar shape",
                throwable: $error
            )->toJsonResponse();
        }
    }


    #[Route('/calendar/day', methods: ['GET'])]
    public function getInterviewsByDays(
        string $interviewId
    )
    {
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving details interviw",
                throwable: $error
            )->toJsonResponse();
        } 
    }
}

