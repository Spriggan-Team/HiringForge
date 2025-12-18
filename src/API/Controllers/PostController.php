<?php

use App\Usecases\Post\PostReader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PostController extends AbstractController
{

    public function __construct(private PostReader $reader){}

    /*======================================
        SELECT REQUEST
    =====================================*/

    #[Route('/posts', methods: ["GET"], name: "fetch_all_post")]
    public function fetchAllPost(): JsonResponse
    {
        try
        {
            return $this->json($this->reader->findAll(), 201);
        }
        catch(Exception $ex){
            echo "Caught Exception: ", $ex->getMessage(), '\n';
            return $this->json([ "message" => "Something went wrong" ]);
        }
    }


    #[Route("/posts/{id}", methods: ["GET"], name: "fetch_one_post")]
    public function fetchOnePost(int $id): JsonResponse
    {
        try{
            $post = $this->reader->findOne($id);
            if(isset($post)){
                return $this->json(["data" => $post], 201);
            }
            else{
                return  $this->json(["message" => "Not Found" ]);
            }
        }
        catch(Exception $ex){
            echo "Caught Exception: ", $ex->getMessage(), '\n';
            return $this->json([ "message" => "Something went wrong" ]);
        }
    }


}