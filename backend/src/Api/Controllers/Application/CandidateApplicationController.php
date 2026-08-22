<?php


namespace App\Api\Controllers\Application;

use Psr\Log\LoggerInterface;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Candidate\ApplyToJobOffer;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;


use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handle user applications services
 */
#[Route('/applications')]
#[IsGranted(AccountRole::CANDIDATE->value)]
class CandidateApplicationController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
        private CandidateRepositoryInterface $candidateRepository
    ){
        ApiResponse::init($logger);
    }

    #[Route('/', methods: ['GET'])]
    public function getApplications()
    {
        try{

        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }

    
    /**
     * Retreive job ids of 
     * all related applications this user
     */
    #[Route('/jobs/ids', methods: ['GET'])]
    public function getApplicationsIds(){
        try{
            /** @var AuthenticatedPerson|null  $candidate*/
            $candidate = $this->getUser();
            
            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorize action",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $candidateId = $candidate->getId();
            $ids = $this->candidateRepository->getCandidateApplicationJobIds(candidateId: $candidateId);

            return ApiResponse::success(
                data: $ids,
                message: "Everything is okay"
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong"
            )->toJsonResponse();
        }
    }



    #[Route('/{jobId}/apply')]
    public function apply(
        string $jobId,
        Request $request,
        ApplyToJobOffer $usecase
    ){
        try{
            /** @var AuthenticatedPerson|null  $user*/
            $user = $this->getUser();

            if(!$user){
                return ApiResponse::error(
                    message: "Unauthorize actions",
                    statusCode: 401
                );
            }
            $body = json_decode($request->getContent(), true);
            $fileId = $body["fileId"];

            if(!$fileId){
                return ApiResponse::error(
                    message: "fileId must be provided",
                    statusCode: 400
                )->toJsonResponse();
            }

            $applicationId = $usecase->execute(
                candidateId: $user->getId(),
                offerId: $jobId,
                fileId: $fileId
            );

            return ApiResponse::success(
                data: $applicationId,
                message: "Everything went right"
            )->toJsonResponse();
        }   
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception
            )->toJsonResponse();
        } 
    }
}