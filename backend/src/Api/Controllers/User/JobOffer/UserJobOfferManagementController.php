<?php

namespace App\Api\Controllers\User\JobOffer;

use Exception;
use DomainException;

use App\Api\Responder\ApiResponse;

use App\Domain\Shared\Account\AccountRole;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Api\Controllers\User\JobOffer\Mapper\CreateJobOfferRequestMapper;


use App\Application\Usecases\JobOffer\JobOfferImageRemover;
use App\Application\Usecases\JobOffer\JobOfferImageUploader;
use App\Application\Usecases\JobOffer\JobOfferEraser;
use App\Application\Usecases\JobOffer\JobOfferModifier;
use App\Application\Usecases\JobOffer\JobOfferRecorder;
use App\Application\Usecases\JobOffer\JobOffferPublisher;
use App\Application\Usecases\JobOffer\MarkJobOfferAsDraft;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;



/**
 * This controller is guarded through JWT control and 
 * user exitence control (check in bdd)
 */
#[Route('/job_offer')]
#[IsGranted(AccountRole::USER->value)]
class UserJobOfferManagementController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    )
    {
        ApiResponse::init($logger);
    }


    #[Route("/", methods: ["POST"], name: "create_job_offer" )]
    public function createJobOffer(
        Request $request,
        JobOfferRecorder $handler,
        CreateJobOfferRequestMapper $mapper
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson */
            $account = $this->getUser();

            $body = json_decode($request->getContent(), true);

            $command = $mapper->fromArray($body);
            $offerId = $handler->execute(
                accountId: $account->getId(),
                command: $command
            );

            return ApiResponse::success(["offerId" => $offerId],"Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $ex){
            return ApiResponse::error(
                message: "Something wrong happenned",
                throwable: $ex
            )->toJsonResponse();
        }
    }




    /**
     * Handles the upload of one or more images for a specific job offer,
     * validating and attaching them to the corresponding offer.
     */
    #[Route('/{offerId}/assets/uploads', methods: ['POST'])]
    public function uploadJobOfferAssets(
        Request $request,
        string $offerId,
        JobOfferImageUploader $jobOfferImageUploader
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson **/
            $user = $this->getUser();

            $files = $request->files->get("images", []);
            $mainImageIndex = $request->request->get('mainIndex', null);

            if($mainImageIndex && !is_int($mainImageIndex))
            {
                return ApiResponse::error("Your mainIndex must be a integer")->toJsonResponse();
            }

            if(!is_array($files))
            {
                return ApiResponse::error("You must send an array of files object for this to function")->toJsonResponse();
            }

            $filesInfo = $jobOfferImageUploader->execute(
                files: $files, 
                offerId: $offerId, 
                userId: $user->getId(),
                mainImageIndex: $mainImageIndex
            );

            return ApiResponse::success(
                        data: ["failed" => $filesInfo]
                   )->toJsonResponse();
        }
        catch(DomainException $domainException)
        {
            return ApiResponse::error(
                        message: $domainException->getMessage() ?? "Something went wrong",
                        throwable: $domainException
                    )->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error("Something went wrong")->toJsonResponse();
        }
    }


    #[Route('/offers/{offerId}/assets/remove', methods: ['POST'])]
    public function removeJobOfferAssets(
        Request $request,
        string $offerId,
        JobOfferImageRemover $assetsRemover
    )
    {
        try
        {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $body = json_decode($request->getContent(), true);
            $filesInfo = $assetsRemover->execute(
                offerId: $offerId,
                accountId: $user->getId(),
                fileNames: $body["fileNames"] ?? []
            );
            return ApiResponse::success(
                        data: $filesInfo,
                        message: "Everything went smoothly"
                    )->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error("Something went wrong")->toJsonResponse();
        }
    }



    #[Route('/offers/{offerId}/draft', methods: ['POST'])]
    public function defineJobOfferAsDraft(
        string $offerId,
        MarkJobOfferAsDraft $usecase
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson **/
            $user = $this->getUser();
            $usecase->execute(
                accountId: $user->getId(),
                offerId: $offerId
            );
            return ApiResponse::success("Everything went smothly")->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error("Something went wrong")->toJsonResponse();
        }
    }


    
    #[Route('/offers/{offerId}/pipeline/{pipelineId}', methods: ['POST'])]
    public function TogglePipeline(
        Request $request,
        string $offerId
    )
    {

    }



    #[Route("/publish/{offerId}", methods: ['POST'])]
    public function publish(
        string $offerId,
        JobOffferPublisher $handler,
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $handler->execute($user->getId(), $offerId);
            return ApiResponse::success("Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $ex){
            return ApiResponse::error( 
                "Something wrong happenned",
                throwable: $ex,
            )->toJsonResponse();
        }
    }





    #[Route("/change", methods: ['PATCH'] ,name: "change_job_offer")]
    public function changeJobOffer(
        Request $request,
        JobOfferModifier $handler
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $body = json_decode($request->getContent(), true);
            $command = new ChangeJobOffferRequest(
                    uuid: $body['uuid'],
                    title: $body['title'],
                    content: $body['content'],
            );

            $handler->execute(
                userId: $user->getId(),
                command: $command
            );
            
            return ApiResponse::success("Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return ApiResponse::error( "Something wrong happenned" )->toJsonResponse();
        }
    }




    #[Route("/job_offer/{offerId}", methods: ['DELETE'] ,name: "job_offer_delete")]
    public function deleteJobOffer(
        string $offerId,
        JobOfferEraser $handler,
    ){
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();
            $handler->execute($offerId, $user->getId());
            return ApiResponse::success("Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return ApiResponse::error(
                "Something wrong happenned"
            );
        }
    }


}


?>