<?php

namespace App\Api\Controllers\Contract;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/contract')]
class ContractTypeController extends AbstractController{   
    public function __construct(
        LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

    #[Route('', name: 'api_contract_types_get', methods: ["GET"])]
    public function get(
        Request $request,
        UserRepositoryInterface $useRepository,
        ContractTypeRepositoryInterface $contractInterface
    ): JsonResponse {
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            $id = $user->getId();
            if (!$user) {
                return  ApiResponse::error(data: ['error' => 'Unauthorized'], statusCode:  Response::HTTP_UNAUTHORIZED)->toJsonResponse();
            }

            $country = $request->query->get('country', 'France');
            $organizationId = $useRepository->getOrganizationId(userId: $user->getId());

            $contractTypes = $contractInterface->getAll(country: $country, organizationId: $organizationId);
            return ApiResponse::success(data: $contractTypes, statusCode:  Response::HTTP_OK)->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong.", 
                throwable: $exception,
            )->toJsonResponse();
        }
    }
}