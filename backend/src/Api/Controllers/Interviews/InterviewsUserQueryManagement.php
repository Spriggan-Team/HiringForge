<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Domain\File\MediaStorageInterface;


use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



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
     * Generate interviews
     * Route: "/interviews/users/agenda"
     * Queries:
     *  - date: ISO String
     *  - skip: number
     *  - limit: number
     */
    #[Route('/agenda', methods: ['GET'])]
    public function getRecentForToday(
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

            $results = $this->interviewsRepository->getTodayInterviewAgenda(
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
     */
    #[Route('/job_offers', methods: ['GET'])]
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
                    $value['candidate']['image'] = $this->resolvePublicImageUrl(
                        request: $request,
                        pathResolver: $this->pathResolver,
                        params: AccountStorageParams::candidateProfileImage(
                            candidateId: $value['candidate']['id'],
                            storedFileName: $value['candidate']['image']['name']
                        ),
                        mime: $value['candidate']['image']['mime']
                    );
                }
                elseif (isset($value['candidate'])) {
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

