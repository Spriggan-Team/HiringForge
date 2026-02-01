<?php

namespace App\Api\Controllers;

use App\Api\DTO\Candidate\RegisterCandidateCommand;
use App\Api\Responder\ApiResponseBuilder;
use  Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use App\Domain\Shared\Address;


use App\Application\Command\Handlers\Auth\UserAuthCommandHandler;
use App\Application\Command\Handlers\Auth\AgentAuthCommandHandler;
use App\Application\Command\Handlers\Auth\CandidateAuthCommandHandler;
use App\Application\Command\Handlers\Auth\DeAuthenticateCommandHandler;

use App\Application\Command\Handlers\User\RegisterUserCommandHandler;
use App\Application\Command\Handlers\Candidate\RegisterCandidateCommandHandler;

use App\Application\DTO\AuthentificateActor;
use App\Application\DTO\User\RegisterUserCommand;

use Exception;



class AuthController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
        ApiResponseBuilder::init($this->logger);
    }


    #[Route('/candidate/login', name: "candidate.login")]
    public function candidateLogin(
        Request $request,
        AuthentificateActor $command,
        CandidateAuthCommandHandler $handler
    ){
        try
        {
            $body = json_decode($request->getContent(), true);
            $command = new AuthentificateActor(
                $body['email'], $body['password']
            );
            $response = $handler->handle($command);
            return $this->json($response, 200);
        } 
        catch (\Throwable $exception) 
        {
            return $this->json(
                ApiResponseBuilder::error("Something wen wrong", $exception),
                400
            );
        }
    }



    #[Route("/users/login", name: "user.login")]
    public function userLogin(
        Request $request,
        UserAuthCommandHandler $handler
    ){
        try
        {
            $body = json_decode($request->getContent(), true);
            $command = new AuthentificateActor(
                $body['email'], $body['password']
            );
            $response = $handler->handle($command);
            return $this->json($response, 200);
        } 
        catch (\Throwable $exception) 
        {
            return $this->json(ApiResponseBuilder::error("Something wen wrong", $exception), 400);
        }
    }



    #[Route('/agent/login', name: 'agent.login')]
    public function agentLogin(
        Request $request,
        AgentAuthCommandHandler $handler
    )
    {
        try{
            $body = json_decode($request->getContent(), true);
            $command = new AuthentificateActor(
                $body['email'], $body['password']
            );
            $response = $handler->handle($command);
            return $this->json($response, 200);
        } 
        catch (\Throwable $exception) 
        {
            return $this->json(ApiResponseBuilder::error("Something wen wrong", $exception), 400);
        }
    }



    #[Route('/logout', name: 'actor.logout')]
    public function logout(
        Request $request,
        DeAuthenticateCommandHandler $handler
    ){
        try{
            $body = json_decode($request->getContent(), true);
            $response = $handler->handle($body['token']);
            return $this->json($response, 200);
        }
        catch(\Throwable $exception)
        {
            return $this->json(ApiResponseBuilder::error("Something wen wrong", $exception), 400);
        }
    }



    #[Route('/candaidate/register', methods: ("POST"))]
    public function candidateRegister(
        Request $request,
        RegisterCandidateCommandHandler $commandhandler
    ): JsonResponse
    {
        try{
            /** @var InputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterCandidateCommand(
                lastName: $inputBag->get('firstName'),
                firstName: $inputBag->get('firstName'),
                email: $inputBag->get('email'),
                password: $inputBag->get('password'),
                image: $request->files->get('image', null),
                cv: $request->files->get('cv', null),
                address: new Address(
                    street: $inputBag->get("address[street]"),
                    city: $inputBag->get("address[city]"),
                    postalCode: $inputBag->get("address[postalCode]"),
                    country: $inputBag->get("address[country]"),
                )
            );
            $response = $commandhandler->handle($command);
            return $this->json($response, 201);
        }
        catch(Exception $exception)
        {
            return $this->json(ApiResponseBuilder::error("Something wen wrong", $exception), 400);
        }
    }



    #[Route("/users/register", methods: ["POST"], name: "register_user")]
    public function userRegister(
        Request $request,
        RegisterUserCommandHandler $commandHandler
    ): JsonResponse
    {
        try {
            /** @var InputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterUserCommand(
                name:  $inputBag->get('name'),
                email: $inputBag->get('email'),
                siret: $inputBag->get('siret'),
                password: $inputBag->get('password'),
                images: $request->files->get('images', []),
                presentation: $request->files->get('presentation',null),
                address: new Address(
                    street: $inputBag->get("address[street]"),
                    city: $inputBag->get("address[city]"),
                    postalCode: $inputBag->get("address[postalCode]"),
                    country: $inputBag->get("address[country]"),
                )
            );
            $response = $commandHandler->handle($command);
            return $this->json($response, 201);
        }
        catch (\Exception $exception)
        {
            return $this->json(ApiResponseBuilder::error('Please check your data fields and formats', $exception), 400);
        }
    }


}