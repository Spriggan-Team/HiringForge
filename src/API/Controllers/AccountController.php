<?php

namespace App\Api\Controllers;


use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\Account\GetAccountRequest;
use App\Api\DTO\Account\CreateAccountRequest;
use App\Api\DTO\Account\DeleteAccountRequest;
use App\Api\DTO\Account\MutateAccountRequest;
use App\Api\DTO\Account\GetAccountCollectionRequest;
use App\Api\DTO\Account\OverwriteAccountRequest;


use App\Application\Command\Handlers\Account\MutateAccountCommandHandler;
use App\Application\Command\Handlers\Account\CreateAccountCommandHandler;
use App\Application\Command\Handlers\Account\DeleteAccountCommandHandler;
use App\Application\Command\Handlers\Account\OverwriteAccountCommandHandler;

use App\Application\Query\Handlers\Account\GetAccountQueryHandler;
use App\Application\Query\Handlers\Account\GetAccountCollectionQueryHandler;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class AccountController extends AbstractController
{

    public function __construct(private LoggerInterface $logger) {}



    #[Route("/users/{uuid}", methods: ["GET"], name: "fetch_one_account")]
    public function getAccountById(
        string $uuid,
        GetAccountQueryHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new GetAccountRequest(uuid: $uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Account not found'), 404);
        }
    }




    #[Route("/users", methods: ['GET'], name: "fetch_all_account")]
    public function getAccountCollection(GetAccountCollectionQueryHandler $handler): JsonResponse
    {
        try {
            $response = $handler->handle(new GetAccountCollectionRequest());
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }





    #[Route("/users", methods: ["POST"], name: "create_account")]
    public function createAccount(
        Request $request,
        CreateAccountCommandHandler $commandHandler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new CreateAccountRequest(
                name:  $data['name'],
                email: $data['email'],
                siret: $data['siret'],
                password: $data['password'],
            );
            $response = $commandHandler->handle($command);

            return $this->json($response, 201);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Something went wrong'), 400);
        }
    }


    
    #[Route("/users/{uuid}", methods:["PATCH"], name: "update_account")]
    public function updateAccount(
        string $uuid,
        MutateAccountCommandHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new MutateAccountRequest(uuid: $uuid));
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
        OverwriteAccountCommandHandler $handler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new OverwriteAccountRequest(
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
        DeleteAccountCommandHandler $handler
    ): JsonResponse
    {
        try {
            $response = $handler->handle(new DeleteAccountRequest(uuid: $uuid));
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }

}

?>