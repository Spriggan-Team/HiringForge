<?php

namespace App\Api\Controllers;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\DTO\RequireAuthentification;
use App\Application\DTO\JobOffer\CreateJobOffer;
use App\Application\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Application\DTO\JobOffer\ChangeJobOffferRequest;


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


class JobOfferController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    ){}

    /*======================================
        SELECT REQUEST
    =====================================*/



    #[Route('/job_offer', methods: ["GET"], name: "fetch_all_job_offer")]
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
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage() . '\n', ["exception"=> $ex]);
            return $this->json(ApiResponseBuilder::error("Something went wrong"), 404 );
        }
    }



    #[Route("/job_offer/{offerId}", methods: ["GET"], name: "fetch_one_job_offer")]
    public function fetchOneJobOffer(
        string $offerId,
        GetJobOfferQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle($offerId);
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
            $command =new CreateJobOffer(
                title:    $body['title'],
                content:  $body['content'],
                categories: $body['categories'],
                image: $request->files->get('image', null),
            );
            $response = $handler->handle($command, $auth);
            return $this->json($response, 201);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }



    #[Route("/job_offer/{offerId}", methods: ['PATCH'])]
    public function publish(
        string $offerId,
        PublishJobOfferCommandHandler $handler,
    )
    {
        try{
            $response = $handler->handle($auth->actorId, $offerId);
            return $this->json($response, 201);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }



    #[Route("/job_offer", methods: ['PATCH'] ,name: "change_job_offer")]
    public function changeJobOffer(
        Request $request,
        ChangeJobOfferCommandHandler $handler
    ){
        try{
            $body = json_decode($request->getContent(), true);
            
            $response = $handler->handle(
                new ChangeJobOffferRequest(
                    uuid: $body['uuid'],
                    title: $body['title'],
                    content: $body['content'],
                    image: $request->files->get("image"),
                ), $auth
            );
            
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }



    #[Route("/job_offer/{offerId}", methods: ['DELETE'] ,name: "job_offer_delete")]
    public function deleteJobOffer(
        string $offerId,
        DeleteJobOfferCommandHandler $handler,
    ){
        try{
            $response = $handler->handle($offerId,$auth);
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }

}


?>