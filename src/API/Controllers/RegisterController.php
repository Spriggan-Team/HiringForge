<?php

namespace App\Api\Controllers;

use App\Api\DTO\Candidate\RegisterCandidateCommand;
use App\Application\DTO\User\RegisterUserCommand;

use App\Domain\Shared\Address;
use App\Api\Responder\ApiResponse;

use App\Application\Command\Handlers\User\RegisterUserCommandHandler;
use App\Application\Command\Handlers\Candidate\RegisterCandidateCommandHandler;


use  Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Exception;




class RegisterController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
        ApiResponse::init($this->logger);
    }


    #[Route('/candaidate/register', methods: ["POST"], name: 'candidate_register')]
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
                address:  Address::tryCreate([
                    'street' => $inputBag->get("address[street]"),
                    'country' => $inputBag->get("address[country]"),
                    'postalCode' => $inputBag->get("address[postalCode]"),
                ]),
                searchRadius:  $inputBag->get("searchRadius")
            );
            $response = $commandhandler->handle($command);
            return $response->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error("Something wen wrong", $exception)->toJsonResponse();
        }
    }



    #[Route("/users/register", methods: ["POST"], name: "user_register")]
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
                videoPresentation: $request->files->get('videoPresentation',null),
                address:  Address::create(
                    street: $inputBag->get("address[street]"),
                    postalCode: $inputBag->get("address[postalCode]"),
                    country: $inputBag->get("address[country]"),
                )
            );
            $response = $commandHandler->handle($command);
            return $response->toJsonResponse();
        }
        catch (\Exception $exception)
        {
            return ApiResponse::error('Please check your data fields and formats', $exception)->toJsonResponse();
        }
    }


}