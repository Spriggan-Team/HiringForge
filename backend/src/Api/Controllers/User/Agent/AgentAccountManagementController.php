<?php

namespace App\Api\Controllers\User\Agent;

use App\Api\Responder\ApiResponse;
use App\Domain\Shared\Account\AccountRole;
use App\Application\DTO\Agent\CreateAgentCommand;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Agent\CreateAgentUseCase;
use App\Application\Usecases\Agent\DeleteAgentUsecase;

use App\Domain\ApplicationErrorCode;
use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\Exception\RessourceAlreadyRegistered;
use App\Domain\Exception\ResourceNotFoundException;

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
    { ApiResponse::init($logger); }

    #[Route("/create", methods: "POST")]
    public function create(
        Request $request,
        CreateAgentUseCase $usecase
    ): JsonResponse{
        try{
            $data = json_decode($request->getContent(), true);
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $agent = new CreateAgentCommand(
                email: $data["email"],
                password: $data["password"],
                verificationCode: $data["verificationCode"],
                authorId: $user->getId()
            );
            $agent = $usecase->execute($agent);

            return ApiResponse::success(data: [
                            "email" => $agent->email(),
                        ], 
                        message: "Agent successfully created" ,
                        statusCode: 201
                    )->toJsonResponse();
        }
        catch(RessourceAlreadyRegistered $foundRessourceError){
            return ApiResponse::error(
                "Something went wrong while execution this route",
                throwable: $foundRessourceError,
                code: ApplicationErrorCode::ACCOUNT_ALREADY_EXISTS
            )->toJsonResponse();
        }
        catch(OTPException $optError){
            return ApiResponse::error(
                message: "Invalid OTP  code. Please ask a new one and try again",
                throwable: $optError
            )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                "Something went wrong while execution this route",
                throwable: $exception
            )->toJsonResponse();
        }        
    }


    #[Route("/delete/{agentId}", methods: "POST")]
    public function delete(
        int $agentId,
        DeleteAgentUsecase $usecase
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();
            $usecase->execute(authorId: $user->getId(), agentId: $agentId);
            
            return ApiResponse::notice(
                        message: "Operation successfully executed"
                    )->toJsonResponse();
        }
        catch(ResourceNotFoundException $notFound){
            return ApiResponse::error(
                        code: ApplicationErrorCode::ACCOUNT_NOT_FOUND,
                        throwable: $notFound
                    )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                    message: "Something went wrong",
                    throwable: $exception
                )->toJsonResponse();
        }        
    }
}