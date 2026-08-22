<?php


namespace App\Api\Controllers\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/candidates/assets')]
class CandidateAssetsController extends AbstractController
{

    public function __construct(
        LoggerInterface $logger,
        private CandidateRepositoryInterface $candidateRepository,
        private AccountRepositoryInterface $accountRepositoryInterface,
        private PathResolverInterface $pathResolver
    ){
        ApiResponse::init($logger);
    }

       
    #[Route('/profile', methods: ['GET'])]
    public function getProfilImage()
    {
        try{
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorize action",
                    statusCode: 401
                )->toJsonResponse();
            }
            $candidateId = $candidate->getId();

            if(!$this->candidateRepository->exists($candidateId)){
                return ApiResponse::error(
                    message: "No such a candidate found"
                )->toJsonResponse();
            };

            $image = $this->accountRepositoryInterface->getProfileImage($candidateId);
            
            /** File path */
            $params = AccountStorageParams::candidateProfileImage(
                candidateId: $candidateId,
                storedFileName: $image->name
            );
                
            $fullPathFile = $this->pathResolver->resolveTargetDirectory(
                mimeType: $image->mime,
                params: $params
            );
                    
            /** Binary response */
            return new BinaryFileResponse(
                $fullPathFile 
            );
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong",
                statusCode: 400
            )->toJsonResponse();
        }
    }


    #[Route('/me/resumes', methods:["GET"])]
    public function getResumes():JsonResponse
    {
        try{
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorierd actions"
                )->toJsonResponse();
            }

            $cvs = $this->candidateRepository->getResumeFiles($candidate->getId());
            $result = [];

            foreach($cvs as $cv){
                $result[]= [
                    'id' => $cv->id, //Static media cv id
                    'name' => $cv->name,
                    'size' => $cv->size,
                    'mimeType' => $cv->mime,
                    'originalName' => $cv->originalName,
                    'createdAt' => $cv->createdAt,
                ];
            }

            return ApiResponse::success(
                data: $result,
                message: 'Everything is okay'
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }


    #[Route('/resumes/{resumeId}/content', methods:['GET'])]
    public function getResumeContent(
        string $resumeId
    )
    {
        try{
            ApiResponse::$logger->error("ROUTE REACHED ");

            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorize action",
                    statusCode: 401
                )->toJsonResponse();
            }

            $candidateId =  $candidate->getId();
            $resume = $this->candidateRepository->findResumeById(candidateId:$candidateId, resumeId: $resumeId);

            if(!$resume){
                return ApiResponse::error(
                    message: "The specified resume does not exist or is not recorded"
                )->toJsonResponse();
            }
            
            $params = AccountStorageParams::resumes(candidateId: $candidateId, storedFileName: $resume->name);
            $fullPathFile = $this->pathResolver->resolveTargetDirectory(
                mimeType: $resume->mime,
                params: $params
            );

            ApiResponse::$logger->error("FULL PATH " . $fullPathFile);
            return new BinaryFileResponse(
                $fullPathFile
            );
        }   
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        } 
    }
}