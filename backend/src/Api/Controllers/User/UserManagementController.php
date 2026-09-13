<?php

namespace App\Api\Controllers\User;


use Exception;

use App\Api\Responder\ApiResponse;

use App\Domain\Shared\Address;
use App\Domain\Shared\Account\AccountRole;

use App\Application\DTO\User\ChangeUserProfileCommand;
use App\Application\DTO\Auth\AuthenticatedPerson;

use App\Application\Usecases\User\UserEraser;
use App\Application\Usecases\User\UserModifier;
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
     * PATCH /change
     * 
     * Updates authenticated user's profile information.
     * Supports changing name, SIRET, address, profile images, and video presentation.
     * Files added or removed are processed via media services with proper validation.
     */

    #[Route("/change", methods: ['PATCH'], name: "change_user_data")]
    public function modify(Request $request, UserModifier $handler): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $formData = $request->request;
            $videoFile = $request->files->get("videoPresentation");

            // $command = ;

            $failedUploads = $handler->execute($command);

            return ApiResponse::success(
                data: ["failedUploads" => $failedUploads],
                message: "User profile updated successfully."
            )->toJsonResponse();

        } catch (\Exception $exception) {
            return ApiResponse::error('Something went wrong', throwable: $exception)->toJsonResponse();
        }
    }


    /**
     * DELETE /delete
     * 
     * Deletes the authenticated user's account.
     * Removes all associated data and media, and invalidates active sessions.
     * This action is irreversible and requires valid authentication.
     */
    #[Route('/delete', methods: ['DELETE'], name: "delete_account")]
    public function deleteAccount(
        Request $request,
        UserEraser $usecase
    ): JsonResponse
    {
        try
        {
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $usecase->execute(
                $user->getId()
            );
            return ApiResponse::success("Everything went smoothly")->toJsonResponse();
        }
        catch (Exception $exception) {
            return ApiResponse::error('Nothing Found',throwable: $exception)->toJsonResponse();
        }
    }

}

?>