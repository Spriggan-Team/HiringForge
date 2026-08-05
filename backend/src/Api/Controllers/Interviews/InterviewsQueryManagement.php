<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Api\Controllers\Helpers\ApiControllerHelpers;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;

use App\Domain\Shared\Account\AccountRole;
use App\Domain\Interviews\InterviewsRepositoryInterface;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/interviews')]
class InterviewsQueryManagement extends AbstractController{

    use ApiControllerHelpers;

    public function __construct(
        LoggerInterface $logger,
        private InterviewsRepositoryInterface $interviewsRepository,
        private MediaStorageInterface $mediaStorage
    )
    {
        ApiResponse::init($logger);
    }


    /**
     * Route /users/job_offer/{offerId}?limit=number&skip=number
     */
    #[IsGranted(AccountRole::USER->value)]
    #[Route('/users/job_offer/{offerId}', methods: ['GET'])]
    public function getUserInterviews(
        string $offerId,
        Request $request
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- Params pagination
            $limit = max(1, filter_var($request->query->get('limit', 15), FILTER_VALIDATE_INT) ?: 15);
            $skip  = max(0, filter_var($request->query->get('skip', 0), FILTER_VALIDATE_INT) ?: 0);

            //-- Fetching interviews with projection
            $results = $this->interviewsRepository->fetchJobInterviewsProjection(
                jobId: $offerId,
                limit: $limit,
                skip: $skip,
                scheme: [
                    'id' => true,
                    'startDate' => true,
                    'minutes' => true,
                    'status' => true,
                    'description' => true,
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

            //-- Mapping image public URL resolution
            $data = array_map(function (array $value) use ($request) {
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
                } elseif (isset($value['candidate'])) {
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
                message: "Something went wrong while fetching interviews",
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }
}