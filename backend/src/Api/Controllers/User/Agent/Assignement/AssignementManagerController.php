<?php

namespace App\Api\Controllers\User\Agent\Assignement;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Agent\CreateAssignementCommand;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Domain\Shared\Account\AccountRole;
use App\Application\Usecases\Agent\Assignement\CreateAssignementUsecase;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;



#[Route("/users/agents/assignement")]
#[IsGranted(AccountRole::USER)]
class AssignementManagerController extends AbstractController
{
    
    #[Route("/", methods: "POST")]
    public function addAssignement(
        Request $request,
        CreateAssignementUsecase $usecase
    ):JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();
            $data = json_decode($request->getContent(), true);

            $result = $usecase->execute(new CreateAssignementCommand(
                agent_id: $data["agent_id"],
            ));

            return ApiResponse::success(
                        data: $result, 
                        message: "Everything went smoothly"
                    )->toJsonResponse();
        }
        catch(\Exception $exception){
            return  ApiResponse::error(
                        message: "Something went wrong",
                        throwable: $exception
                    )->toJsonResponse();
        }
    }

    #[Route("/update", methods: "POST")]
    public function updateAssignementScope(){

    }

    #[Route("/delete", methods: "DELETE")]
    public function deleteAssignement(){

    }
}