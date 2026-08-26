<?php

namespace App\Api\Controllers\User\JobOffer;


use App\Api\Responder\ApiResponse;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




#[Route('/users/job_offers')]
class JobOfferQueryManagement extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferQueryRepositoryInterface $queryRepository,
        private ApplicationRepositoryInterface $applicationRepository,
        private EmploymentOfferRepositoryInterface $offerRepository
    ) {
        ApiResponse::init($logger);
    }

        
    /**
     * Route: users/applications/kpis?jobId=string
     * Obtained kpis about job(s), if jobId is specified then the calcul is done only within the scope 
     * of the targeted job offer
     */
    #[Route('/kpis', methods: ['GET'])]
    public function getJobKpis(
        Request $request
    ): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 401
                )->toJsonResponse();
            }

            $userId = $user->getId();
            $jobOfferId = $request->query->get('jobId', null);

            //-- Dynamic construction of search criteria
            $baseCriteria = array_filter([
                'userId'     => $userId,
                'jobOfferId' => $jobOfferId,
            ], fn($value) => $value !== null);

            //  Total number of applications associated with this job posting and this user
            $totalApplications = $this->applicationRepository->countApplications($baseCriteria);

            // Candidates Not Selected for This Position
            $rejectedCandidatesCount = $this->applicationRepository->countApplications(array_merge($baseCriteria,[
                'status'     => JobApplicationStatus::REJECTED,
            ]));

            $rejectionRate = $totalApplications > 0
                ? (int) round(($rejectedCandidatesCount / $totalApplications) * 100)
                : 0;

            //  Bids generated and accepted for this specific bid
            $offersDeclined = $this->offerRepository->countOffers(array_merge($baseCriteria,[
                'status'     => EmploymentOfferStatus::DECLINED,
            ]));


            $offersAccepted = $this->offerRepository->countOffers(array_merge($baseCriteria,[
                'status'     => EmploymentOfferStatus::ACCEPTED,
            ]));

            $offersGenerated = $this->offerRepository->countOffers($baseCriteria);


            //  Delay in hiring
            $avgTimeToHireDays = $this->applicationRepository->getAvgTimeToHireDays(
                jobOfferId: $jobOfferId,
                userId: $userId
            );
            $userAvgTimeToHireDays = $this->applicationRepository->getUserAvgTimeToHireDays($userId);

            //-- If no job is provided the difference is null
            $avgTimeToHireDiffDays = $jobOfferId !== null 
                ? ($avgTimeToHireDays - $userAvgTimeToHireDays) 
                : 0;

            // Weekly increase specific to this offer
            $now = new \DateTimeImmutable();
            $startOfThisWeek = $now->modify('monday this week 00:00:00');
            $startOfLastWeek = $startOfThisWeek->modify('-7 days');
            $endOfLastWeek   = $startOfThisWeek->modify('-1 second');

            $thisWeekCount = $this->applicationRepository->countApplicationsInPeriod(
                userId: $userId,
                start: $startOfThisWeek,
                end: $now,
                jobOfferId: $jobOfferId
            );

            $lastWeekCount = $this->applicationRepository->countApplicationsInPeriod(
                userId: $userId,
                start: $startOfLastWeek,
                end: $endOfLastWeek,
                jobOfferId: $jobOfferId
            );

            $applicationIncreaseThisWeek = $lastWeekCount > 0
                        ? (int) round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100)
                        : ($thisWeekCount > 0 ? 100 : 0);

            return ApiResponse::success(
                data: [
                    'totalApplications'           => $totalApplications,
                    'applicationIncreaseThisWeek' => $applicationIncreaseThisWeek,
                    'rejectionRate'               => $rejectionRate,
                    'rejectedCandidatesCount'     => $rejectedCandidatesCount,
                    'offersDeclined'              => $offersDeclined,
                    'offersAccepted'              => $offersAccepted,
                    'offersGenerated'             => $offersGenerated,
                    'avgTimeToHireDays'           => $avgTimeToHireDays,
                    'avgTimeToHireDiffDays'       => $avgTimeToHireDiffDays,
                ],
                message: 'Job KPIs retrieved successfully'
            )->toJsonResponse();

        }
        catch (\Exception $error) {
            return ApiResponse::error(
                message: 'Failed to fetch job KPIs: ' . $error->getMessage(),
                statusCode: 500
            )->toJsonResponse();
        }
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

        }
        catch (\Throwable $exception) {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception,
                statusCode: 500
            )->toJsonResponse();
        }
    }


    /**
     * Route: /stats?jobId=string
     * If jobId is specified, the stats (overview) values are calculated using the scope of that specific job.
     * If jobId is omitted, stats are calculated for all jobs belonging to the recruiter.
     */
    #[Route('/stats', name: 'api_users_job_offers_stats', methods: ['GET'])]
    public function getJobOfferOverview(
        Request $request
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            $jobId = $request->query->get('jobId');

            $data = $this->queryRepository->getJobStats(
                userId: $user->getId(),
                jobId: $jobId
            );
            // $this->logger->error("Controller REACH");


            return ApiResponse::success(
                message: 'Everything is ok',
                data: $data
            )->toJsonResponse();
        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while fetching job overview',
                throwable: $error,
                verbose: true
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




    #[Route('/kanban', methods: ['GET'])]
    public function getKanbanResult()
    {
        try{

        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong gettings job kanbans"
            );
        }
    }





 
}