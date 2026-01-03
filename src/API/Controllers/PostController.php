<?php

namespace App\Api\Controllers;

use Exception;

use App\Api\DTO\Post\GetPostRequest;
use App\Api\DTO\Post\CreatePostRequest;
use App\Api\DTO\Post\DeletePostRequest;
use App\Api\DTO\Post\MutatePostRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Api\DTO\Post\GetPostCollectiontRequest;


use App\Application\Command\Handlers\Post\CreatePostCommandHandler;
use App\Application\Command\Handlers\Post\DeletePostCommandHandler;
use App\Application\Command\Handlers\Post\MutatePostCommandHandler;


use App\Application\Query\Handlers\Post\GetPostQueryHandler;
use App\Application\Query\Handlers\Post\GetPostsCollectionQueryHandler;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PostController extends AbstractController
{

    public function __construct(private LoggerInterface $logger){}

    /*======================================
        SELECT REQUEST
    =====================================*/



    #[Route('/posts/{accountId}', methods: ["GET"], name: "fetch_all_post")]
    public function fetchAllPost(
        int $accountId,
        GetPostsCollectionQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle(new GetPostCollectiontRequest(accountId: $accountId));
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage() . '\n', ["exception"=> $ex]);
            return $this->json(ApiResponseBuilder::error("Something went wrong"), 404 );
        }
    }



    #[Route("/posts/{accountId}", methods: ["GET"], name: "fetch_one_post")]
    public function fetchOnePost(
        string $accountId,
        Request $request,
        GetPostQueryHandler $handler
    ): JsonResponse
    {
        try{
            $response = $handler->handle(new GetPostRequest(accountId: $accountId, uuid: $request->query->get("uuid")));
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


    #[Route("/posts", methods: ["POST"], name: "create_post" )]
    public function createPost(
        Request $request,
        CreatePostCommandHandler $handler
    ): JsonResponse
    {
        try{
            $body = json_decode($request->getContent(), true);
            $command =new CreatePostRequest(
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




    #[Route("/posts", methods: ['PATCH'] ,name: "")]
    public function updatePost(
        Request $request,
        MutatePostCommandHandler $handler
    ){
        try{
            $body = json_decode($request->getContent(), true);
            
            $response = $handler->handle(
                new MutatePostRequest(
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


    #[Route("/posts/{accountId}", methods: ['DELETE'] ,name: "")]
    public function deletePost(
        string $accountId,
        Request $request,
        DeletePostCommandHandler $handler
    ){
        try{
            $response = $handler->handle( new DeletePostRequest( accountId: $accountId, uuid: $request->query->get("uuid") ) );
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(ApiResponseBuilder::error( "Something wrong happenned" ), 400);
        }
    }

    


}


?>