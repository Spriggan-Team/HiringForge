<?php


namespace App\Api\Controllers\Application;

use Psr\Log\LoggerInterface;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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


    #[Route('{jobId}/apply')]
    public function apply(
        string $jobId,
        Request $request
    ){
        try{
            
            $user = $this->getUser();

            if(!$user){
                return ApiResponse::error(
                    message: "Unauthorize actions",
                    statusCode: 401
                );
            }
            $body = json_decode($request->getContent(), true);
            $resumeId = $body["resumeId"];

            if(!$resumeId){
                return ApiResponse::error(
                    message: "resumeId must be provided",
                    statusCode: 400
                )->toJsonResponse();
            }


        }   
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception
            )->toJsonResponse();
        } 
    }
}