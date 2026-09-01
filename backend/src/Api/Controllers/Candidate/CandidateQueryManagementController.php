<?php

namespace App\Api\Controllers\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Candidate\CandidateSkillRepositoryInterface;

use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Shared\Account\AccountRole;

use Psr\Log\LoggerInterface;

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
        LoggerInterface $logger,
        private CandidateRepositoryInterface $candidateRepository
    ){
        ApiResponse::init($logger);
    }
    
    #[Route('/profile/desc', methods: ["GET"])]
    public function getCandidateDescription(){
        try{
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if (!$candidate) {
                return ApiResponse::error(
                    message: "Unauthorized action",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $desc = $this->candidateRepository->getDescription($candidate->getId());

            return ApiResponse::success(
                data: [
                    "desc" => $desc
                ]
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "An error occurred while processing your request",
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                throwable: $error,
            )->toJsonResponse();
        }
    }



    #[Route("/profile/skills", methods: ["GET"])]
    public function getCandidateSkills(
        CandidateSkillRepositoryInterface $candidateSkillRepo
    ): JsonResponse {
        try{
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if (!$candidate) {
                return ApiResponse::error(
                    message: "Unauthorized action",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $skills = $candidateSkillRepo->getCandidateSkills($candidate->getId()) ?? [];

            $result = array_map(fn($skill) => ([
                "id" => $skill->id(),
                "name" => $skill->name()
            ]) ,$skills);

            // ApiResponse::$logger->error("SKILLS : ". json_encode($result));
            return ApiResponse::success(
                data: $result
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "An error occurred while processing your request",
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                throwable: $error,
            )->toJsonResponse();
        }
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
        catch (ResourceNotFoundException $e) { 
            return ApiResponse::error(
                message: "Candidate not found",
                statusCode: Response::HTTP_NOT_FOUND,
                throwable: $e
            )->toJsonResponse();

        }
        catch (\Exception $e) { 
            return ApiResponse::error(
                message: "An error occurred while processing your request",
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
                throwable: $e,
            )->toJsonResponse();
        }
    }


}