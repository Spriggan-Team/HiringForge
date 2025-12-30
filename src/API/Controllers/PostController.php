<?php

namespace App\Api\Controllers;

use Exception;

use App\Api\DTO\Post\GetPosCollectiontRequest;

use App\Api\DTO\Post\GetPostRequest;
use App\Application\Command\Handlers\Post\CreatePostCommandHandler;
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



    #[Route('/posts', methods: ["GET"], name: "fetch_all_post")]
    public function fetchAllPost(
        GetPostsCollectionQueryHandler $handler
    ): JsonResponse
    {
        try
        {
            $response = $handler->handle(new GetPosCollectiontRequest());
            return $this->json($response, 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage() . '\n', ["exception"=> $ex]);
            return $this->json([ "message" => "Something went wrong" ], 404 );
        }
    }



    #[Route("/posts/{uuid}", methods: ["GET"], name: "fetch_one_post")]
    public function fetchOnePost(
        string $uuid,
        GetPostQueryHandler $query
    ): JsonResponse
    {
        try{
            $post = $query->handle(new GetPostRequest(uuid: $uuid));
            if($post != null)
                return $this->json(["data" => $post], 200);
            else
                return $this->json([ "message" => "Not Found" ], 200);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(). '\n', ['exception'=>$ex]);
            return $this->json([ "message" => "Not found" ], 404);
        }
    }


    /*======================================
        INSERT REQUEST
    =====================================*/


    #[Route("/posts", methods: ["POST"], name: "create_post" )]
    public function createPost(
        Request $request,
        CreatePostCommandHandler $command
    ): JsonResponse
    {
        try{
            $body = json_decode($request->getContent(), true);
            $command->handle($body);
            return $this->json(["message" => "operation sucessefully executed"], 201);
        }
        catch(Exception $ex){
            $this->logger->error("Caught Exception: ". $ex->getMessage(), ['exception'=> $ex]);
            return $this->json(["message" => "Something wrong happenned" ], 400);
        }
    }


}


?>