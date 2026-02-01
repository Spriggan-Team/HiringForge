<?php

namespace App\Api\Controllers;


use Exception;

use App\Api\Responder\ApiResponseBuilder;


use App\Api\DTO\User\DeleteUserRequest;
use App\Api\DTO\User\MutateUserRequest;
use App\Api\DTO\User\OverwriteUserRequest;


use App\Application\Command\Handlers\User\DeleteUserCommandHandler;
use App\Application\Command\Handlers\User\MutateUserCommandHandler;
use App\Application\Command\Handlers\User\OverwriteUserCommandHandler;
use App\Application\Query\Handlers\User\GetUserQueryHandler;
use App\Domain\User\UserId;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class UserController extends AbstractController
{

    public function __construct(private LoggerInterface $logger) {
        ApiResponseBuilder::init($logger);
    }


    #[Route("/users/{uuid}", methods: ["GET"], name: "fetch.user")]
    public function getAccountById(
        string $uuid,
        GetUserQueryHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new UserId($uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            return $this->json(ApiResponseBuilder::error('User not found', $exception), 404);
        }
    }



    
    #[Route("/users/{uuid}", methods:["PATCH"], name: "update_user")]
    public function updateUser(
        string $uuid,
        Request $request,
        MutateUserCommandHandler $handler
    ): JsonResponse
    {
        try {
            $playload = json_decode($request->getContent(), true);
            $command = new MutateUserRequest(
                uuid: $uuid,
                name: $playload['name'] ?? null,
                password: $playload['password'] ?? null,
                siret: $playload['siret'] ?? null
            );
            $response = $handler->handle($command);
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