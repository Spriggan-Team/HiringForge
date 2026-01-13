<?php

namespace App\Api\Controllers;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\JobOffer\GetJobOfferRequest;
use App\Api\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Api\DTO\JobOffer\CreateJobOfferRequest;
use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Api\DTO\JobOffer\MutateJobOfferRequest;


use App\Application\Command\Handlers\JobOffer\CreateJobOfferCommandHandler;
use App\Application\Command\Handlers\JobOffer\DeleteJobOfferCommandHandler;
use App\Application\Command\Handlers\JobOffer\MutateJobOfferCommandHandler;


use App\Application\Query\Handlers\JobOffer\GetJobOfferCollectionQueryHandler;
use App\Application\Query\Handlers\JobOffer\GetJobOfferQueryHandler;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class JobOfferController extends AbstractController
{

    public function __construct(private LoggerInterface $logger){}

    /*======================================
        SELECT REQUEST
    =====================================*/



    #[Route('/job_offer/{accountId}', methods: ["GET"], name: "fetch_all_job_offer")]
    public function fetchAllJobOffer(
        int $accountId,
        GetJobOfferCollectionQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle(new GetJobOfferCollectiontRequest(accountId: $accountId));
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage() . '\n', ["exception"=> $ex]);
            return $this->json(ApiResponseBuilder::error("Something went wrong"), 404 );
        }
    }



    #[Route("/job_offer/{accountId}", methods: ["GET"], name: "fetch_one_job_offer")]
    public function fetchOneJobOffer(
        string $accountId,
        Request $request,
        GetJobOfferQueryHandler $handler
    ): JsonResponse
    {
        try{
            $response = $handler->handle(new GetJobOfferRequest(accountId: $accountId, uuid: $request->query->get("uuid")));
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(). '\n', ['exception'=>$ex]);
            return $this->json(ApiResponseBuilder::error("Something went Found"), 404);
        }
    }


    /*======================================
        INSERT/UPDATE/DELETE  REQUEST
    =====================================*/


    #[Route("/job_offer", methods: ["POST"], name: "create_job_offer" )]
    public function createJobOffer(
        Request $request,
        CreateJobOfferCommandHandler $handler
    ): JsonResponse
    {
        try{
            $body = json_decode($request->getContent(), true);
            $command =new CreateJobOfferRequest(
                title: $body['title'],
                content: $body['content'],
                accountId: $body["accountId"],
            );

            $response = $handler->handle($command);
            return $this->json($response, 201);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }




    #[Route("/job_offer", methods: ['PATCH'] ,name: "change_job_offer")]
    public function updateJobOffer(
        Request $request,
        MutateJobOfferCommandHandler $handler
    ){
        try{
            $body = json_decode($request->getContent(), true);
            
            $response = $handler->handle(
                new MutateJobOfferRequest(
                    accountId: $body['accountId'],
                    uuid: $body['uuid'],
                    title: $body['title'],
                    content: $body['content']
                )
            );
            
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }



    #[Route("/job_offer/{accountId}", methods: ['DELETE'] ,name: "")]
    public function deleteJobOffer(
        string $accountId,
        Request $request,
        DeleteJobOfferCommandHandler $handler
    ){
        try{
            $response = $handler->handle( new DeleteJobOfferRequest( accountId: $accountId, uuid: $request->query->get("uuid") ) );
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }

}


?>