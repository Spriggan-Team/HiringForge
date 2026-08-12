<?php

namespace App\Api\Controllers\Application;


use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\Offer\OfferRepositoryInterface;
use App\Domain\Offer\OfferStatus;
use App\Domain\Shared\Account\AccountRole;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/users/applications')]
class UserApplicationQueryController extends AbstractController
{
    use ApiControllerHelpers;

    public function __construct(
        private LoggerInterface $logger,
        private ApplicationRepositoryInterface $applicationRepository,
        private MediaStorageInterface $mediaStorage,
        private OfferRepositoryInterface $offerRepository
    )
    {
        ApiResponse::init($logger);
    }
    

    #[IsGranted(AccountRole::USER->value)]
    #[Route('/{jobOfferId}/rejected', methods: ['GET'])]
    public function countReject(
        string $jobOfferId
    ){
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $rejectedCount = $this->applicationRepository->countApplications(criteria: [
                'userId' => $user->getId(),
                'jobOfferId' => $jobOfferId,
                'status' => JobApplicationStatus::REJECTED
            ]);

            return ApiResponse::success(
                data: $rejectedCount,
                message: 'Everything is fine'
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong',
                statusCode: 400,
                verbose: true
            )->toJsonResponse();
        }
    }


    /**
     * /applications/serach?text=string&status=string&candidate
     */
    #[Route('/search', methods: ['GET'])]
    public function search(){
        try{
            
        }
        catch(\Exception $error)
        {
            return ApiResponse::error(
                message: "Something went wrong"
            )->toJsonResponse();
        }
    }


    /**
     * Retreive aggregate of applications
     * 
     * Route /job_offer?jobId=string&limit=number&skip=number
     * 
     * Retreive all application related to an user if jobId. Howerver
     * has it been provided the search is done only with the specified job as a  scope
     */
    #[IsGranted(AccountRole::USER->value)]
    #[Route('/job_offers', methods: ['GET'])]
    public function getApplications(
        Request $request,
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);
            $jobId = $request->query->get('jobId', null);

            //-- Fetching with projection
            $results = $this->applicationRepository->fetchJobApplicationsProjection(
                jobId: $jobId,
                limit: $limit,
                skip: $skip,
                scheme: [
                    'id' => true,
                    'status' => true,
                    'matchScore' => true,
                    'appliedAt' => true,
                    'candidate' => [
                        'id' => true,
                        'email' => true,
                        'firstName' => true,
                        'lastName' => true,
                        'image' => [
                            'name' => true,
                            'mime' => true,
                        ],
                    ],
                    'jobOffer' => $jobId ? [
                            'id' => true,
                            'title' => true
                    ] : null
                ],
                userId: $user->getId()
            );

            //-- Mapping image resolution safely
            $data = array_map(function (array $value) use ($request) {
                // Check if candidate and image data exist before resolving public URL
                if (isset($value['candidate']['image']['name'], $value['candidate']['image']['mime'])) {
                    $value['candidate']['image'] = $this->resolveUrl(
                        request: $request,
                        ownerId: $value['candidate']['id'],
                        projectDir: '...',
                        fileName: $value['candidate']['image']['name'],
                        mimeType: $value['candidate']['image']['mime'],
                        mediaStorage: $this->mediaStorage,
                        ownerType: MediaOwnerType::CANDIDATE,
                        purpose: MediaPurpose::PROFILE
                    );
                }
                else if (isset($value['candidate'])) {
                    // set to null if no image exists
                    $value['candidate']['image'] = null;
                }

                return $value;
            }, $results);

            return ApiResponse::success(
                data: $data,
                statusCode: 200
            )->toJsonResponse();
        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: "Something went wrong",
                statusCode: 400,
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
                'status'     => OfferStatus::DECLINED,
            ]));


            $offersAccepted = $this->offerRepository->countOffers(array_merge($baseCriteria,[
                'status'     => OfferStatus::ACCEPTED,
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


    /**
     * Route: /candidates/stats?jobId=string&timeframe=month|week
     * if jobId noot specified the resarch is done within all offers related to an user
     */
    #[Route('/stats', methods: ['GET'])]
    public function getJobOfferMetrics(
        Request $request
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $jobId = $request->query->get("jobId", null);
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
                throwable: $error,
                verbose: true
            )->toJsonResponse();
        }
    }
    

}