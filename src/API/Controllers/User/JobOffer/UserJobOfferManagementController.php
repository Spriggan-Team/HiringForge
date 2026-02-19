<?php

namespace App\Api\Controllers\User\JobOffer;

use Exception;

use App\Api\Responder\ApiResponse;

use App\Application\DTO\JobOffer\CreateJobOffer;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Domain\Shared\Account\AccountRole;

use App\Application\Command\Usecase\JobOffer\JobOfferModifier;
use App\Application\Command\Usecase\JobOffer\JobOfferRecorder;
use App\Application\Usecases\User\JobOffer\UserJobOfferEraser;
use App\Application\Usecases\User\JobOffer\UserJobOffferPublisher;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/job_offer')]
#[IsGranted(AccountRole::USER->value)]
class UserJobOfferManagementController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    ){
        ApiResponse::init($logger);
    }


    #[Route("/", methods: ["POST"], name: "create_job_offer" )]
    public function createJobOffer(
        Request $request,
        JobOfferRecorder $handler
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $account = $this->getUser();

            $body = json_decode($request->getContent(), true);
            $command =new CreateJobOffer(
                title:    $body['title'],
                content:  $body['content'],
                categories: $body['categories'], //an array of categories' ids
            );
            $handler->execute(
                accountId: $account->getId(),
                command: $command
            );;
            return ApiResponse::success("Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $ex){
            return ApiResponse::error(
                message: "Something wrong happenned",
                throwable: $ex
            )->toJsonResponse();
        }
    }


    #[Route("/publish/{offerId}", methods: ['PATCH'])]
    public function publish(
        string $offerId,
        UserJobOffferPublisher $handler,
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
                    image: $request->files->get("image"),
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
        UserJobOfferEraser $handler,
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