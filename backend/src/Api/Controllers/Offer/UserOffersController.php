<?php


namespace App\Api\Controllers\Offer;

use App\Api\Controllers\Offer\Maper\CreateOfferRequestMapper;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\DTO\Offer\CreateOfferRequestDto;
use App\Application\Usecases\Offer\CreateOffer;
use App\Domain\Offer\OfferRepositoryInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


#[Route('/offers/user')]
class UserOffersController extends AbstractController
{
    public function __construct(
        LoggerInterface $logger,
        private ValidatorInterface $validator,
        private OfferRepositoryInterface $offerRepository
    ){
        ApiResponse::init($logger);
    }


    /**
     * Route /offers/user/jobs?jobId=string&limit=number&skip=number&companyId=number
     */
    #[Route('/jobs/', methods: ['GET'])]
    public function getOfferForUser(Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $limit = max(1, $request->query->getInt('limit', 17));
            $skip = max(0, $request->query->getInt('skip', 0));
            $companyId = $request->query->get("companyId", null);
            $jobId = $request->query->get("jobId", null);
            
            $scheme = [
                'id' => true,
                'title' => true,
                'status' => true,
                'sentAt' => true,
                'expiredAt' => true,
                'salary' => true,
                'candidate' => [
                    'id' => true,
                    'firstName' => true,
                    'lastName' => true,
                    'email' => true,
                    'image' => [
                        'name' => true,
                        'mime' => true
                    ]
                ],
                'application' => [
                    'id' => true,
                ],
                'jobOffer' => [
                    'id' => true,
                    'title' => true
                ]
            ];

            // Call repository
            $result = $this->offerRepository->fetchOfferProjection(
                userId: $user->getId(),
                companyId: $companyId,
                jobId: $jobId, 
                scheme: $scheme,
                limit: $limit,
                skip: $skip
            );

            return ApiResponse::success(
                data: $result,
                message: 'Everything is OK'
            )->toJsonResponse();

        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something wrong happened',
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            )->toJsonResponse();
        }
    }



    #[Route('/', methods: ['POST'])]
    public function createOffer(
        #[MapRequestPayload] CreateOfferRequestDto $command,
        CreateOffer $usecase
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            
            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized',
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $errors = $this->validator->validate($command);

            if (count($errors) > 0) {
                $formattedErrors = [];
                foreach ($errors as $error) {
                    $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                return ApiResponse::error(
                    message: 'Validation failed',
                    data: $formattedErrors,
                    statusCode: Response::HTTP_BAD_REQUEST
                )->toJsonResponse();
            }

            $result = $usecase->execute(userId: $user->getId() ,command: $command);
            
            return ApiResponse::success(
                data: $result,
                message: 'Offer created successfully',
                statusCode: Response::HTTP_CREATED
            )->toJsonResponse();

        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong: ' . $error->getMessage(),
                statusCode: Response::HTTP_BAD_REQUEST
            )->toJsonResponse();
        }
    }

}
