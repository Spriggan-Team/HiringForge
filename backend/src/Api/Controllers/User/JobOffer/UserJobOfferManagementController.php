<?php

namespace App\Api\Controllers\User\JobOffer;

use Exception;
use DomainException;

use App\Api\Responder\ApiResponse;

use App\Domain\Shared\Account\AccountRole;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Api\Controllers\User\JobOffer\Mapper\CreateJobOfferRequestMapper;


use App\Application\Usecases\JobOffer\JobOfferEraser;
use App\Application\Usecases\JobOffer\JobOfferModifier;
use App\Application\Usecases\JobOffer\JobOfferRecorder;
use App\Application\Usecases\JobOffer\JobOffferPublisher;
use App\Application\Usecases\JobOffer\MarkJobOfferAsDraft;
use App\Application\Usecases\JobOffer\JobOfferImageRemover;
use App\Application\Usecases\JobOffer\JobOfferImageUploader;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



/**
 * This controller is guarded through JWT control and 
 * user exitence control (check in bdd)
 */
#[Route('/job_offer')]
class UserJobOfferManagementController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    )
    {
        ApiResponse::init($logger);
    }


    #[Route("", methods: ["POST"], name: "create_job_offer" )]
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
        catch(Exception $e){
            dump($e->getMessage());
            dump($e->getTraceAsString());
            return ApiResponse::error(
                message: "Something wrong happenned",
                throwable: $e
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
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $files = $request->files->get('images', []);
            $rawMainIndex = $request->request->get('mainIndex');

            $mainImageIndex = null;
            if ($rawMainIndex !== null && $rawMainIndex !== '') {
                if (!ctype_digit((string) $rawMainIndex)) {
                    return ApiResponse::error('Your mainIndex must be a valid integer')->toJsonResponse();
                }
                $mainImageIndex = (int) $rawMainIndex;
            }

            if (!is_array($files)) {
                $files = [$files]; // Enclose as a table if only one file was sent
            }

            if (empty($files)) {
                return ApiResponse::error('No image files provided in the "images" key')->toJsonResponse();
            }

            $uploadResult = $jobOfferImageUploader->execute(
                offerId: $offerId,
                userId: $user->getId(),
                files: $files,
                mainImageIndex: $mainImageIndex
            );

            return ApiResponse::success(data: [
                'success_count' => count($uploadResult['success']),
                'failed_count' => count($uploadResult['failed']),
                'failed' => $uploadResult['failed'],
            ])->toJsonResponse();

        }
        catch (DomainException $domainException) {
            return ApiResponse::error(
                message: $domainException->getMessage() ?: 'Something went wrong',
                throwable: $domainException
            )->toJsonResponse();
        } catch (Exception $e) {

            return ApiResponse::error('Something went wrong', throwable: $e)->toJsonResponse();
        }
    }




    #[Route('/offers/{offerId}/assets/remove', methods: ['POST'])]
    public function removeJobOfferAssets(
        Request $request,
        string $offerId,
        JobOfferImageRemover $assetsRemover,
        LoggerInterface $logger
    ) {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $body = json_decode($request->getContent(), true) ?? [];
            $fileNames = is_array($body['fileNames'] ?? null) ? $body['fileNames'] : [];

            if (empty($fileNames)) {
                return ApiResponse::error("No file names provided", statusCode: 400)->toJsonResponse();
            }

            $filesInfo = $assetsRemover->execute(
                accountId: $user->getId(),
                offerId: $offerId,
                fileNames: $fileNames
            );

            return ApiResponse::success(
                data: $filesInfo,
                message: "Assets removal process completed"
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            $logger->error("Error removing job offer assets: " . $exception->getMessage(), [
                'exception' => $exception,
                'offerId' => $offerId,
            ]);

            return ApiResponse::error("Something went wrong", statusCode: 500)->toJsonResponse();
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