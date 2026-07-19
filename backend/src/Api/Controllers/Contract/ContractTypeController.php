<?php

namespace App\Api\Controllers\Contract;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;


use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/contract')]
class ContractTypeController extends AbstractController{    
#[Route('', name: 'api_contract_types_get', methods: ["GET"])]
    public function get(
        Request $request,
        UserRepositoryInterface $useRepository,
        ContractTypeRepositoryInterface $contractInterface
    ): JsonResponse {
        /** @var AuthenticatedPerson|null $user */
        $user = $this->getUser();

        if (!$user) {
            return  ApiResponse::error(data: ['error' => 'Unauthorized'], statusCode:  Response::HTTP_UNAUTHORIZED)->toJsonResponse();
        }

        $organizationId = $useRepository->getOrganizationId(userId: $user->getId());
        $country = $request->query->get('country', 'France');

        $contractTypes = $contractInterface->getAll($country, $organizationId);

        return $this->json($contractTypes, Response::HTTP_OK);
    }
}