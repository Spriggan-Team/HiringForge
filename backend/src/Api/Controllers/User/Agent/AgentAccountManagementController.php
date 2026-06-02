<?php

namespace App\Api\Controllers\User\Agent;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Agent\CreateAgentCommand;
use App\Application\Usecases\Agent\CreateAgentUseCase;
use App\Domain\Shared\Account\AccountRole;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/users/agents")]
#[IsGranted(AccountRole::USER)]
class AgentAccountManagementController extends AbstractController{

    public function __construct(
        private LoggerInterface $logger
    )
    {
        ApiResponse::init($logger);
    }

    
    #[Route("/create", methods: "POST")]
    public function create(
        Request $request,
        CreateAgentUseCase $usecase
    ): JsonResponse{
        try{
            $data = json_decode($request->getContent(), true);
            $agent = new CreateAgentCommand(email: $data["email"], password: $data["password"], verificationCode: $data["verificationCode"]);
            $agent = $usecase->execute($agent);

            return ApiResponse::success(data: [
                            "email" => $agent->email(),
                        ], 
                        message: "Agent successfully created" ,
                        statusCode: 201
                    )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error("Something went wrong while execution this route", throwable: $exception)->toJsonResponse();
        }        
    }
}