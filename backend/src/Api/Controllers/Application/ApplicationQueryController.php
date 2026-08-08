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
use App\Domain\Shared\Account\AccountRole;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/applications')]
class ApplicationQueryController extends AbstractController
{
    use ApiControllerHelpers;

    public function __construct(
        private LoggerInterface $logger,
        private ApplicationRepositoryInterface $applicationRepository,
        private MediaStorageInterface $mediaStorage
    )
    {
        ApiResponse::init($logger);
    }
    

    #[IsGranted(AccountRole::USER->value)]
    #[Route('/{jobOfferId}/rejected', methods: ['GET'])]
    public function getRejected(
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

            $rejectedCount = $this->applicationRepository->count(criteria: [
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
                statusCode: 400
            )->toJsonResponse();
        }
    }



    /**
     * Route /job_offer?jobOfferId=string&limit=number&skip=number
     */
    #[IsGranted(AccountRole::USER->value)]
    #[Route('/job_offer', methods: ['GET'])]
    public function getApplicationForJob(
        Request $request,
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);
            $jobOfferId = $request->query->get('jobOfferId', null);

            //-- Fetching with projection
            $results = $this->applicationRepository->fetchJobApplicationsProjection(
                jobId: $jobOfferId,
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



    #[Route('/{jobOfferId}/kpis', methods: ['GET'])]
    public function getJobKpis(string $jobOfferId): JsonResponse
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

            //  Total number of applications associated with this job posting and this user
            $totalApplications = $this->applicationRepository->count([
                'jobOfferId' => $jobOfferId,
                'userId'     => $userId,
            ]);

            // Candidates Not Selected for This Position
            $rejectedCandidatesCount = $this->applicationRepository->count([
                'jobOfferId' => $jobOfferId,
                'userId'     => $userId,
                'status'     => JobApplicationStatus::REJECTED,
            ]);

            $rejectionRate = $totalApplications > 0
                ? (int) round(($rejectedCandidatesCount / $totalApplications) * 100)
                : 0;

            //  Bids generated and accepted for this specific bid
            $offersGenerated = $this->applicationRepository->count([
                'jobOfferId' => $jobOfferId,
                'userId'     => $userId,
                'status'     => JobApplicationStatus::OFFER_DECLINED,
            ]);

            $offersAccepted = $this->applicationRepository->count([
                'jobOfferId' => $jobOfferId,
                'userId'     => $userId,
                'status'     => JobApplicationStatus::HIRED,
            ]);

            //  Average time to hire for a specific job posting vs. the recruiter's overall average
            $avgTimeToHireDays = $this->applicationRepository->getAvgTimeToHireDays($jobOfferId, $userId);
            $userAvgTimeToHireDays = $this->applicationRepository->getUserAvgTimeToHireDays($userId);

            // Difference in days compared to the recruiter's overall average (e.g., -2)
            $avgTimeToHireDiffDays = $avgTimeToHireDays - $userAvgTimeToHireDays;

            // Weekly increase specific to this offer
            $now = new \DateTimeImmutable();
            $startOfThisWeek = $now->modify('monday this week 00:00:00');
            $startOfLastWeek = $startOfThisWeek->modify('-7 days');
            $endOfLastWeek   = $startOfThisWeek->modify('-1 second');

            $thisWeekCount = $this->applicationRepository->countApplicationsInPeriod(
                $jobOfferId,
                $startOfThisWeek,
                $now
            );

            $lastWeekCount = $this->applicationRepository->countApplicationsInPeriod(
                $jobOfferId,
                $startOfLastWeek,
                $endOfLastWeek
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
                    'offersGenerated'             => $offersGenerated,
                    'offersAccepted'              => $offersAccepted,
                    'avgTimeToHireDays'           => $avgTimeToHireDays,
                    'avgTimeToHireDiffDays'       => $avgTimeToHireDiffDays,
                ],
                message: 'Job KPIs retrieved successfully'
            )->toJsonResponse();

        }
        catch (\Exception $error) {
            $this->logger->error('Une erreur est survenue', [
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'stack' => $error->getTraceAsString(),
            ]);
            return ApiResponse::error(
                message: 'Failed to fetch job KPIs: ' . $error->getMessage(),
                statusCode: 500
            )->toJsonResponse();
        }
    }



}