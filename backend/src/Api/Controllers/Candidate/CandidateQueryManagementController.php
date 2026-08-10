<?php

namespace App\Api\Controllers\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Account\AccountRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This one is used for candidate management;
 * It requires CANDIDATE permissions
 */
#[IsGranted(AccountRole::CANDIDATE->value)]
#[Route("/candidates")]
class CandidateQueryManagementController extends AbstractController
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository
    ){}

    /**
     * This controller allow any connected user to access to its informations
     */
    #[Route('/candidate/profile/{candidateId}', methods: ['GET'])]
    public function getCandidateInformation()
    {

    }

    public function changeProfilInformation()
    {

    }


    #[Route("", methods: ["GET"])]
    public function getCandidatesContext(): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if (!$candidate) {
                return ApiResponse::error(
                    message: "Unauthorized action",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $data = $this->candidateRepository->getCandidateLightModel(candidateId: $candidate->getId());


            return ApiResponse::success(
                data: $data,
                message: "Context retrieved successfully"
            )->toJsonResponse();

        }
        catch (RessourceNotFound $e) { 
            return ApiResponse::error(
                message: "Candidate not found",
                statusCode: Response::HTTP_NOT_FOUND
            )->toJsonResponse();

        }
        catch (\Throwable $e) { 
            return ApiResponse::error(
                message: "An error occurred while processing your request",
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            )->toJsonResponse();
        }
    }

}