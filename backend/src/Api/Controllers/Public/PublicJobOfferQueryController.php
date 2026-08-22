<?php

namespace   App\Api\Controllers\Public;

use App\Api\Responder\ApiResponse;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/public')]
class PublicJobOfferQueryController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferQueryRepositoryInterface $queryRepository
    ) {
        ApiResponse::init($logger);
    }
    

    #[Route('/job_offers', methods: ['GET'])]
    public function retrievePublicJobSummary(Request $request): JsonResponse
    {
        try {
            $locale = $request->query->get('locale', 'fr');
            $search = $request->query->get('search');
            $address = $request->query->get('address');

            $data = $this->queryRepository->fetchPublicJobOffersSummary(
                locale: $locale,
                search: $search,
                address: $address
            );

            return ApiResponse::success(
                data: $data,
                message: 'Public job offers fetched successfully'
            )->toJsonResponse();

        } catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while fetching job offers',
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }



    #[Route('/job_offers/{jobId}', methods: ['GET'])]
    public function getPublicJobDetails(
        string $jobId,
        Request $request
    ): JsonResponse {
        try {
            $locale = $request->query->get('locale', 'fr');

            $data = $this->queryRepository->fetchPublicJobOfferDetail(
                jobOfferId: $jobId,
                locale: $locale
            );

            if ($data === null) {
                return ApiResponse::error(
                    message: 'Job offer not found or not published',
                    statusCode: 404
                )->toJsonResponse();
            }

            return ApiResponse::success(
                data: $data,
                message: 'Job offer details fetched successfully'
            )->toJsonResponse();

        } catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while fetching job details',
                statusCode: 500,
                throwable: $error
            )->toJsonResponse();
        }
    }
}