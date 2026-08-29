<?php

namespace App\Api\Controllers\Application;


use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Application\Query\JobOffer\Repositories\RecruiterJobOfferQueryRepositoryInterface;
use App\Application\Usecases\Application\BulkApplicationStatusChange;
use App\Application\Usecases\Application\ChangeApplicationStatus;
use App\Application\Usecases\Application\GetCandidateByApplication;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;


use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;


use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    )
    {
        ApiResponse::init($logger);
    }

    /**
     * Retrieve candidate pipeline
     */
    #[Route('/pipeline', methods: ['GET'])]
    public function getCandidatesPipeline(
        Request $request,
        ApplicationRepositoryInterface $repository
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

            // pagination parameters
            $skip = max(0, $request->query->getInt('skip', 0));
            $limit = max(1, $request->query->getInt('limit', 5));

            $results = $repository->getCandidatesPipeline(
                recruiterId: $user->getId(),
                skip: $skip,
                limit: $limit 
            );

            return ApiResponse::success(
                data: $results
            )->toJsonResponse();

        } catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while retrieving the pipeline',
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Route : /users/applications/candidates/search?query=string
     * Search candidates withing the system using application as root
     */
    #[Route('/candidates/search', methods: ['GET'])]
    public function search(
        Request $request,
        GetCandidateByApplication $handler
    ){
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $result = [];
            $search = $request->query->get('query', null);

            if($search !== null && trim($search) !== ''){
                ApiResponse::$logger->error("Candidats: ". json_encode($result) );
                $result = $handler->execute(
                    userId: $user->getId(),
                    query: $search
                );
            }

            return ApiResponse::success(
                message: "Not implemeneted",
                data: $result
            )->toJsonResponse();
        }
        catch(\Exception $error)
        {
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }



    #[Route('/status/change/bulk', methods: ["PATCH"])]
    public function bulkChangeApplicationStatus(
        Request $request,
        BulkApplicationStatusChange $handler
    ){
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $ids = $body["ids"];

            if(!is_array($ids)){
                throw new \InvalidArgumentException();
            }
            
            $newStatus = JobApplicationStatus::from($body["newStatus"]);

            $handler->execute(
                ids: $ids,
                userId: $user->getId(),
                newStatus: $newStatus
            );

            return ApiResponse::notice(
                "Something "
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong while changing (bulk) candidate application',
                statusCode: 400,
                verbose: true,
                throwable: $error
            )->toJsonResponse();
        }
    }

    /**
     * Chane status of an application
     */
    #[Route('/{applicationId}/status/change', methods: ["PATCH"])]
    public function changeApplicationStatus(
        Request $request,
        string $applicationId,
        ChangeApplicationStatus $changeAppStatus
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

            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            
            $changeAppStatus->execute(
                userId: $user->getId(),
                applicationId: $applicationId,
                newStatus: JobApplicationStatus::from($body['newStatus'])
            );

            return ApiResponse::notice("Everything went successfully")->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong while changing candidate application',
                statusCode: 400,
                verbose: true,
                throwable: $error
            )->toJsonResponse();
        }
    }
    

    #[IsGranted(AccountRole::USER->value)]
    #[Route('/{jobOfferId}/rejected', methods: ['GET'])]
    public function countReject(
        string $jobOfferId
    ){
        try{
            /** @var AuthenticatedPerson|null $user */
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
     * Retreive aggregate of applications
     * 
     * Route /job_offer?jobId=string&limit=number&skip=number&search
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
            $userId = $user->getId();

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);
            
            $jobId = $request->query->get('jobId', null);
            $search =  $request->request->get('search');

            //-- Fetching with projection
            $results = $this->applicationRepository->fetchJobApplicationsProjection(
                jobId: $jobId,
                limit: $limit,
                skip: $skip,
                search: $search,
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
                userId: $userId 
            );

            //-- Mapping date txt
            $data = $results = array_map(
                static function (array $row): array {
                    if (
                        isset($row['appliedAt'])
                        && $row['appliedAt'] instanceof \DateTimeInterface
                    ) {
                        $row['appliedAt'] = $row['appliedAt']->format(\DateTimeInterface::ATOM);
                    }

                    return $row;
                },
                $results
            );

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
     * Retreive image of an applicant of requested
     * by a recruiter
     */
    #[Route('/{applicationId}/candidates/{candidateId}/profile-image', methods: ['GET'])]
    public function getCandidateImage(
        string $candidateId,
        string $applicationId,
        ApplicationRepositoryInterface $repository,
        AccountRepositoryInterface $accountRepo,
        PathResolverInterface $pathResolver
    ){
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            //-- Check if requriements
            $repository->assertRecruiterHasAccessToApplication(
                recruiterId: $user->getId(),
                candidateId: $candidateId,
                applicationId: $applicationId
            );

            $media = $accountRepo->getProfileImage($candidateId);
            
            if ($media === null) {
                return ApiResponse::error(
                    message: 'No profile image found for this user.',
                    statusCode: 404
                )->toJsonResponse();
            }
            
            $fullPath = $pathResolver->resolveStoragePath(
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
                message: "Something went wrong while fectching candidate iamge",
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Access to candidate resume
     * has its is necessary for treating application (recruiter)
     */
    #[Route('/{applicationId}/candidates/{candidateId}/resume', methods: ['GET'])]
    public function getCandidateResume(
        string $candidateId,
        string $applicationId,
        ApplicationRepositoryInterface $repository,
        PathResolverInterface $pathResolver
    ) {
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            
            //-- Check if requriements
            $repository->assertRecruiterHasAccessToApplication(
                recruiterId: $user->getId(),
                candidateId: $candidateId,
                applicationId: $applicationId
            );

            $media = $repository->getResumeFile(
                candidateId: $candidateId,
                applicationId: $applicationId
            );
            
            if ($media === null) {
                return ApiResponse::error(
                    message: 'No profile image found for this user.',
                    statusCode: 404
                )->toJsonResponse();
            }
            
            $fullPath = $pathResolver->resolveFilePath(
                params: AccountStorageParams::resumes(
                    candidateId: $candidateId,
                    storedFileName: $media->name
                ),
                mimeType: $media->mime
            );

            return new BinaryFileResponse($fullPath);
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while fectching candidate resume for candidate",
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Route: users/job_offer/stats?jobId=string&timeframe=month|week
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
                message: 'Something went wrong while fetching candidate statistics (postualtion metrics)',
                statusCode: 400,
                throwable: $error,
                verbose: true
            )->toJsonResponse();
        }
    }

}