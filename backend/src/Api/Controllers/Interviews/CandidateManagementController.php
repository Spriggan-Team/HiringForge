<?php


namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Interviews\ConfirmInterview;
use App\Application\Usecases\Interviews\RefuseInterview;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;



#[Route("/interviews/candidates")]
class CandidateManagementController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }


    #[Route("/accept/{interviewId}", methods: ["PATCH"])]
    public function accept(
        string $interviewId,
        ConfirmInterview $handler
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            $handler->execute(
                candidateId: $candidateId,
                interviewId: $interviewId
            );

            return ApiResponse::notice(
                message: "Everithing went successfully"
            )->toJsonResponse();
        }
        catch(\Throwable $th){
            return ApiResponse::error(
                message: "Something went wrong while accepting interviews",
                throwable: $th
            )->toJsonResponse();
        }
    }



    #[Route("/refuse/{interviewId}", methods: ["PATCH"])]
    public function refuse(
        Request $request,
        string $interviewId,
        RefuseInterview $handler,
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $rejectionReason = $body['rejectionReason'] ?? null;
            if ($rejectionReason !== null && !is_string($rejectionReason)) {
                throw new \DomainException(
                    'rejectionReason must be a string if provided'
                );
            }

            $handler->execute(
                candidateId: $candidateId,
                interviewId: $interviewId,
                data: [
                    'rejectionReason' => $rejectionReason
                ]
            );

            return ApiResponse::notice(
                message: "Everithing went successfully"
            )->toJsonResponse();
        }
        catch(\Throwable $th){
            return ApiResponse::error(
                message: "Something went wrong while refusing interviews",
                throwable: $th
            )->toJsonResponse();
        }
    }
}