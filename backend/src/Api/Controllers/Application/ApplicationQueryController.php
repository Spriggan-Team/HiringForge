<?php

namespace App\Api\Controllers\Application;


use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
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

        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong',
                statusCode: 400
            )->toJsonResponse();
        }
    }


/**
     * Route /job_offer/{jobOfferId}?limit=number&skip=number
     */
    #[IsGranted(AccountRole::USER->value)]
    #[Route('/job_offer/{jobOfferId}', methods: ['GET'])]
    public function getApplicationForJob(
        string $jobOfferId,
        Request $request,
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);

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
                    $value['candidate']['image'] = $this->resolvePublicUrl(
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

}