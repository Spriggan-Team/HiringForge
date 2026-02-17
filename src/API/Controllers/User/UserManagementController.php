<?php

namespace App\Api\Controllers\User;


use Exception;

use App\Domain\Shared\Address;

use App\Api\Responder\ApiResponse;
use App\Application\Command\Handlers\Account\ChangeAccountEmailCommandHandler;
use App\Application\Command\Handlers\Account\DeleteAccountCommandHandler;
use App\Application\Command\Handlers\User\ChangeUserProfilCommandHandler;


use App\Application\DTO\ChangeEmail;
use App\Application\DTO\User\ChangeUserProfileCommand;
use App\Application\Command\Utils\AuthenticatedPerson;


use App\Domain\Shared\Account\AccountRole;

use App\Application\Query\Handlers\User\GetUserQueryHandler;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted(AccountRole::USER->value)]
#[Route('/users')]
class UserManagementController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponse::init($logger);
    }

    /**
     * This one allow you to get a  users' information with the appropriate persmission
     */
    #[Route("/", methods: ["GET"], name: "fetch_user")]
    public function getUserById(
        Request $request,
        GetUserQueryHandler $handler
    ): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $response = $handler->handle($user->getId());
            return $response->toJsonResponse();
        }
        catch (Exception $exception)
        {
            return ApiResponse::error('User not found', $exception)->toJsonResponse();
        }
    }


    #[Route("/change", methods: ['PATCH'], name: "change_user_data")]
    public function modify(
        Request $request,
        ChangeUserProfilCommandHandler $handler
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $formData = $request->request;
            $command = new ChangeUserProfileCommand(
                uuid: $user->getId(),
                name: $formData->get('name'),
                siret: $formData->get('siret'),
                addImages: $formData->get('images[add]') ?? [],
                deleteImages: $formData->get('images[delete]') ?? [],
                videoPresentation: $request->files->get("videoPresentation"),
                address: Address::create(
                    street:     $formData->get('address[street]'),
                    postalCode: $formData->get("address[postalCode]"),
                    country:    $formData->get("address[country]")
                )
            );
            $response = $handler->handle($command);
            return $response->toJsonResponse();
        }
        catch(\Exception $exception)
        {
            return ApiResponse::error('Something went wrong',$exception)->toJsonResponse();
        }
    }


    #[Route('/delete', methods: ['DELETE'], name: "delete_account")]
    public function deleteAccount(
        Request $request,
        DeleteAccountCommandHandler $handler
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $response = $handler->handle(
                $user->getId(), AccountRole::USER
            );
            return $response->toJsonResponse();
        }
        catch (Exception $exception) {
            return ApiResponse::error('Nothing Found',$exception)->toJsonResponse();
        }
    }

}

?>