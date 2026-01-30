<?php

namespace App\Api\Controllers;

use App\Api\DTO\Candidate\CreateCandidateRequest;
use  Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use App\Domain\Shared\ValueObject\Address;
use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\User\CreateUserRequest;

use App\Application\Command\Handlers\User\CreateUserCommandHandler;
use App\Application\Command\Handlers\Candidate\CreateCandidateCommandHandler;
use Exception;

class AuthController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {}


    #[Route("/login", name: "login")]
    public function login(){

    }

    public function logout(){}


    #[Route('/candaidate/register', methods: ("POST"))]
    public function candidateRegister(
        Request $request,
        CreateCandidateCommandHandler $command
    ): JsonResponse
    {
        try{
            $request = new CreateCandidateRequest();
            $response = $command->handle($request);
            return $this->json($response, 201);
        }
        catch(Exception $exception){
            $this->logger->error(
                "Caught Exception: ". $exception->getMessage(), 
                [   
                    'exception'=>$exception,
                ]
            );
            return $this->json(ApiResponseBuilder::error("Something wen wrong"), 400);
        }
    }

    #[Route("/users/register", methods: ["POST"], name: "register_user")]
    public function userRegister(
        Request $request,
        CreateUserCommandHandler $commandHandler
    ): JsonResponse
    {
        try {
            /** @var InputBag FormData stored in request by Symfony  */
            $request = $request->request;

            $command = new CreateUserRequest(
                name:  $request->get('name'),
                email: $request->get('email'),
                siret: $request->get('siret'),
                password: $request->get('password'),
                images: $request->get('images', []),
                address: new Address(
                    street: $request->get("address[street]"),
                    city: $request->get("address[city]"),
                    postalCode: $request->get("address[postalCode]"),
                    country: $request->get("address[country]"),
                )
            );
            $response = $commandHandler->handle($command);
            return $this->json($response, 201);
        }
        catch (\Exception $exception) {
            $this->logger->error(
                "Caught Exception: ". $exception->getMessage(), 
                [   
                    'exception'=>$exception,
                ]
            );
            return $this->json(ApiResponseBuilder::error('Please check your data fields and formats'), 400);
        }
    }


}