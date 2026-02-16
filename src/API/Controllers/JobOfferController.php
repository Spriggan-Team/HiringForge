<?php

namespace App\Api\Controllers;

use Exception;

use App\Api\Responder\ApiResponse;

use App\Application\DTO\JobOffer\CreateJobOffer;
use App\Application\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Application\Command\Utils\AuthenticatedPerson;

use App\Domain\Shared\Account\AccountRole;

use App\Application\Command\Handlers\JobOffer\CreateJobOfferCommandHandler;
use App\Application\Command\Handlers\JobOffer\ChangeJobOfferCommandHandler;
use App\Application\Command\Handlers\JobOffer\DeleteJobOfferCommandHandler;
use App\Application\Command\Handlers\JobOffer\PublishJobOfferCommandHandler;

use App\Application\Query\Handlers\JobOffer\GetJobOfferCollectionQueryHandler;
use App\Application\Query\Handlers\JobOffer\GetJobOfferQueryHandler;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/job_offer')]
class JobOfferController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    ){}

    /*======================================
        SELECT REQUEST
    =====================================*/



    #[Route('/', methods: ["GET"], name: "fetch_all_job_offer")]
    public function fetchAllJobOffer(
        Request $request,
        GetJobOfferCollectionQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle(new GetJobOfferCollectiontRequest(
                skip: $request->query->get('skip'),
                limit: $request->query->get('limit')
            ));
            return $response->toJsonResponse();
        }
        catch(Exception $ex)
        {
            return ApiResponse::error(message: "Something went wrong", throwable: $ex, statusCode:404  )->toJsonResponse();
        }
    }



    #[Route("/{offerId}", methods: ["GET"], name: "fetch_one_job_offer")]
    public function fetchOneJobOffer(
        string $offerId,
        GetJobOfferQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle($offerId);
            return $response->toJsonResponse();
        }
        catch(Exception $ex){
            return  ApiResponse::error(
                message: "Something went Found",
                throwable: $ex
            )->toJsonResponse();
        }
    }


    /*======================================
        INSERT/UPDATE/DELETE  REQUEST
    =====================================*/

    #[IsGranted(AccountRole::USER->value)]
    #[Route("/", methods: ["POST"], name: "create_job_offer" )]
    public function createJobOffer(
        Request $request,
        CreateJobOfferCommandHandler $handler
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
            $response = $handler->handle($command, $account->getId(), $account->getRoles());
            return $response->toJsonResponse();
        }
        catch(Exception $ex){
            return ApiResponse::error(
                message: "Something wrong happenned",
                throwable: $ex
            )->toJsonResponse();
        }
    }


    #[IsGranted(AccountRole::USER->value)]
    #[Route("/publish/{offerId}", methods: ['PATCH'])]
    public function publish(
        string $offerId,
        PublishJobOfferCommandHandler $handler,
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $response = $handler->handle($user->getId(), $offerId);
            return $response->toJsonResponse();
        }
        catch(Exception $ex){
            return ApiResponse::error( 
                "Something wrong happenned",
                throwable: $ex,
            )->toJsonResponse();
        }
    }


    #[IsGranted(AccountRole::USER->value)]
    #[Route("/change", methods: ['PATCH'] ,name: "change_job_offer")]
    public function changeJobOffer(
        Request $request,
        ChangeJobOfferCommandHandler $handler
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $body = json_decode($request->getContent(), true);
            
            $response = $handler->handle(
                new ChangeJobOffferRequest(
                    uuid: $body['uuid'],
                    title: $body['title'],
                    content: $body['content'],
                    image: $request->files->get("image"),
                ), $user->getId()
            );
            
            return $response->toJsonResponse();
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return ApiResponse::error( "Something wrong happenned" )->toJsonResponse();
        }
    }


    #[IsGranted(AccountRole::USER->value)]
    #[Route("/job_offer/{offerId}", methods: ['DELETE'] ,name: "job_offer_delete")]
    public function deleteJobOffer(
        string $offerId,
        DeleteJobOfferCommandHandler $handler,
    ){
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $response = $handler->handle(
                $offerId,
                $user->getId()
            );
            return $this->json($response, 200);
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