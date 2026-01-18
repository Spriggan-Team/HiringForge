<?php

namespace App\Api\Controllers;


use Exception;

use App\Api\Responder\ApiResponseBuilder;


use App\Api\DTO\User\CreateUserRequest;
use App\Api\DTO\User\DeleteUserRequest;
use App\Api\DTO\User\GetUserCollectionRequest;
use App\Api\DTO\User\GetUserRequest;
use App\Api\DTO\User\MutateUserRequest;
use App\Api\DTO\User\OverwriteUserRequest;


use App\Application\Command\Handlers\User\CreateUserCommandHandler;
use App\Application\Command\Handlers\User\DeleteUserCommandHandler;
use App\Application\Command\Handlers\User\MutateUserCommandHandler;
use App\Application\Command\Handlers\User\OverwriteUserCommandHandler;
use App\Application\Query\Handlers\User\GetUserCollectionQueryHandler;
use App\Application\Query\Handlers\User\GetUserQueryHandler;
use App\Domain\Shared\ValueObject\Address;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class UserController extends AbstractController
{

    public function __construct(private LoggerInterface $logger) {}


    #[Route("/users", methods: ['GET'], name: "fetch_all_users")]
    public function getUsersCollection(GetUserCollectionQueryHandler $handler): JsonResponse
    {
        try {
            $response = $handler->handle(new GetUserCollectionRequest());
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }

    

    #[Route("/users/{uuid}", methods: ["GET"], name: "fetch_one_user")]
    public function getAccountById(
        string $uuid,
        GetUserQueryHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new GetUserRequest(uuid: $uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Account not found'), 404);
        }
    }




    #[Route("/users", methods: ["POST"], name: "register_user")]
    public function createUser(
        Request $request,
        CreateUserCommandHandler $commandHandler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new CreateUserRequest(
                name:  $data['name'],
                email: $data['email'],
                siret: $data['siret'],
                password: $data['password'],
                imagePath: $data['imagePath'],
                address: new Address(
                    $data['address']['street'],
                    $data['address']['city'], 
                    $data['address']['postalCode'], 
                    $data['address']['country']
                )
            );
            $response = $commandHandler->handle($command);

            return $this->json($response, 201);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Something went wrong'), 400);
        }
    }


    
    #[Route("/users/{uuid}", methods:["PATCH"], name: "update_user")]
    public function updateUser(
        string $uuid,
        MutateUserCommandHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new MutateUserRequest(uuid: $uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }


    
    #[Route("/users/{uuid}", methods:["PUT"], name: "update_account")]
    public function overwriteAccount(
        string $uuid,
        Request $request,
        OverwriteUserCommandHandler $handler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new OverwriteUserRequest(
                uuid:  $uuid,
                name:  $data['name'],
                email: $data['email'],
                siret: $data['siret'],
                password: $data['password'],
            );

            $response = $handler->handle($command);
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }



    
    #[Route('/delete/{uuid}', methods: ['DELETE'], name: "delete_account")]
    public function deleteAccount(
        string $uuid,
        DeleteUserCommandHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new DeleteUserRequest(uuid: $uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }

}

?>