<?php

namespace App\Api\Controllers\User\JobOffer;

use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Application\Query\JobOffer\Repositories\JobOfferAnalyticsRepositoryInterface;
use App\Application\Query\JobOffer\Repositories\RecruiterJobOfferQueryRepositoryInterface;

use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\User\Repositories\UserDashboardQueryRepositoryInterface;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;

use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;
use App\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


#[Route('/users/job_offers')]
class JobOfferQueryManagement extends AbstractController
{
    use ApiControllerHelpers;

    public function __construct(
        private LoggerInterface $logger,
        private RecruiterJobOfferQueryRepositoryInterface $queryRepository,
        private ApplicationRepositoryInterface $applicationRepository,
        private EmploymentOfferRepositoryInterface $offerRepository,
        private PathResolverInterface $pathResolver,
        private UserRepositoryInterface $userRepository
    ) {
        ApiResponse::init($logger);
    }

    
    /**
     * Route: /users/job_offers/stats/dashboard
     *      This one is used for retrieving cross stats data
     *      regarding interviews and candidates
     * Queries:
     *      - pick: string (Date ISO)
     *      - timeframe?: "week" | "month" | "year"
     */
    #[Route('/stats/dashboard', methods: ['GET'])]
    public function getRecruitmentStatistics(
        Request $request,
        UserDashboardQueryRepositoryInterface $repository
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 401
                )->toJsonResponse();
            }

            $pick = $request->query->get('pick');
            if (!$pick) {
                return ApiResponse::error(
                    message: '"pick" query parameter is mandatory',
                    statusCode: 400
                )->toJsonResponse();
            }

            try {
                $date = new \DateTimeImmutable($pick);
            } catch (\Exception) {
                return ApiResponse::error(
                    message: 'Invalid date format for "pick". Expected a valid ISO string',
                    statusCode: 400
                )->toJsonResponse();
            }

            $allowedTimeframes = ['week', 'month', 'year'];
            $timeframe = $request->query->get('timeframe', 'week');

            if (!in_array($timeframe, $allowedTimeframes, true)) {
                return ApiResponse::error(
                    message: sprintf('Invalid "timeframe". Allowed values are: %s', implode(', ', $allowedTimeframes)),
                    statusCode: 400
                )->toJsonResponse();
            }

            $results = $repository->getDashboardStats(
                recruiterId: $user->getId(),
                date: $date,
                timeframe: $timeframe
            );

            return ApiResponse::success(
                data: $results,
                message: 'Dashboard statistics retrieved successfully'
            )->toJsonResponse();

        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while retrieving dashboard statistics',
                throwable: $error
            )->toJsonResponse();
        }
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


    /** Count job offers */
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



    #[Route('/summary/{jobId}', methods: ['GET'])]
        public function getSpecificJobOfferSummary(
            string $jobId
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if (!$user) {
                return ApiResponse::error(
                    message: "Unauthenticated action",
                    statusCode: Response::HTTP_UNAUTHORIZED // 401
                )->toJsonResponse();
            }

            /** @var array{candidatesCount: int, interviewsCount: int, employmentOfferCount: int, hiredCount: int, viewsCount: int} $data */
            $data = $this->queryRepository->fetchJobOfferCardinalities(
                recruiterId: $user->getId(),
                jobId: $jobId
            );

            return ApiResponse::success(
                data: $data,
                message: "Everything went successfully"
            )->toJsonResponse();
        }
        catch (\Throwable $exception) {
            $this->logger->error('Error job offer summary: ' . $exception->getMessage(), [
                'exception' => $exception,
                'jobId' => $jobId,
            ]);

            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR // 500
            )->toJsonResponse();
        }
    }


    /**
     * Queries:
     *  Filters:
     *       - publishedState: JobPublicationStatus,
     *       - salary: int,
     *       - candidateCount: int,
     *       - searchText: string,
     *       - searchAddress: string
     *  Pagination:
     *       - skip: int,
     *       - limit: int
     */
    #[Route('/summary', methods: ['GET'])]
    public function retrieveJobOfferSummaryWithPagination(
        Request $request,
        LoggerInterface $logger,
        RecruiterJobOfferQueryRepositoryInterface $queryRepository,
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $user */
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
            $data = $queryRepository->fetchJobOfferViewCollection(
                userId: $user->getId(),
                limit: $limit,
                skip: $skip,
                criteria: $criteria
            );

            return ApiResponse::success(
                data: $data,
                message: "Everything went successfully"
            )->toJsonResponse();

        }
        catch (\Throwable $exception) {
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


 

    /**
     * Route: /stats?jobId=string
     * If jobId is specified, the stats (overview) values are calculated using the scope of that specific job.
     * If jobId is omitted, stats are calculated for all jobs belonging to the recruiter.
     */
    #[Route('/stats', name: 'api_users_job_offers_stats', methods: ['GET'])]
    public function getJobOfferOverview(
        Request $request,
        JobOfferAnalyticsRepositoryInterface $queryRepository
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            $jobId = $request->query->get('jobId');

            $data = $queryRepository->getJobStats(
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

    /**
     * Retrieve job offers wit hspecific criteria
     * Route: /users/job_offers/criteria_base
     * Queries:
     *  - actvityStatus : the activity status of the candidats
     *  - skip (Optionnel): skip range of job offer
     *  - limit : set limit of result count
     */
    #[Route('/criteria_base', name: 'app_job_offers_by_criteria', methods: ['GET'])]
    public function getJobOffersWithCriteria(
        Request $request,
        RecruiterJobOfferQueryRepositoryInterface $repository
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if (!$user) {
                return ApiResponse::error(
                    message: "Unauthenticated action",
                    statusCode: 401
                )->toJsonResponse();
            }


            //-- Queries
            $activityStatus = JobActivityStatus::tryFrom($request->query->get('activityStatus', ''));
            if (!$activityStatus) {
                return ApiResponse::error(
                    message: "Wrong input sent for retrieving job offers by criteria",
                    statusCode: 400
                )->toJsonResponse(); 
            }


            $skip = $request->query->get("skip");
            $limit = $request->query->get("limit");
            $skip = ctype_digit($skip) ? (int) $skip : 0;
            $limit = ctype_digit($limit) ? (int) $limit : 0;

            $userId= $user->getId();
            $views = $repository->getJobOfferLightViewModelByCriteria(
                userId: $userId,
                activityStatus: $activityStatus
            );

            $companyId = $this->userRepository->getOrganizationId($userId);

            // Treat list
            $views = array_map(function($view) use ($request, $companyId) {
                if ($view->image !== null) {
                    $imageUrl = $this->resolvePublicImageUrl(
                        request: $request,
                        params: AccountStorageParams::companyJobImages(
                            companyId: $companyId,
                            storedFileName: $view->image
                        ),
                        pathResolver: $this->pathResolver
                    );

                    $view->image = $imageUrl; 
                }

                return $view;
            }, $views);

            return ApiResponse::success(
                data: $views
            )->toJsonResponse();
        }
        catch (\Exception $error) {
            return ApiResponse::error(
                message: "Something went wrong while looking for matching job offers",
                throwable: $error
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
                'mainImageFileId' => true,
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

}