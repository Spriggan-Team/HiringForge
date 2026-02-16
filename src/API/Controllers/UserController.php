<?php

namespace App\Api\Controllers;


use Exception;

use App\Domain\Shared\Address;

use App\Api\Responder\ApiResponse;
use App\Application\Command\Handlers\Account\DeleteAccountCommandHandler;
use App\Application\Command\Handlers\Account\ResetPasswordCommandHandler;
use App\Application\Command\Handlers\Account\SendVerificationCodeCommandHandler;
use App\Application\Command\Handlers\User\ChangeUserEmailCommandHandler;
use App\Application\Command\Handlers\User\ChangeUserProfilCommandHandler;


use App\Application\DTO\ChangeEmail;
use App\Application\DTO\ChangePassword;
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



#[Route('/users')]
class UserController extends AbstractController
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
    #[IsGranted(AccountRole::USER->value)]
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


    /**
     * This one allow a user to change its email;
     * The requirements here, are to submit the old email along side the new one.
     * You must provide the password and be authentificated here too, because just the authentification
     * is not secure enough if the user momentary/temporary lost its devices (and as for other concerns)...
     */
    #[IsGranted(AccountRole::USER->value)]
    #[Route('/change/email', methods: ['PATCH'], name: "")]
    public function changeEmail(
        Request $request,
        ChangeUserEmailCommandHandler $handler
    )
    {
        try
        {
            $body = json_decode($request->getContent());
            $command = new ChangeEmail(
                oldEMail: $body['oldEmail'],
                newEmail: $body['newEmail'],
                password: $body['password']
            );

            $response = $handler->handle($command);
            return $response->toJsonResponse();
        }
        catch(\Throwable $th)
        {
            return ApiResponse::error('User not found', $th, 404)->toJsonResponse();
        }
    }



    #[Route("/password/verificationcode", methods:["PUT"], name: "update_account")]
    public function resetPasswordVerificationCode(
        Request $request,
        SendVerificationCodeCommandHandler $handler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $response = $handler->handle($data['email'], AccountRole::USER);
            return $response->toJsonResponse();
        }
        catch (Exception $exception) {
            return ApiResponse::error(
                message:'Nothing Found',
                statusCode: 400, throwable: $exception
            )->toJsonResponse();
        }
    }

    

    #[Route("/resetpassword", methods:["PATCH"], name: "reset_password")]
    public function resetPassword(
        Request $request,
        ResetPasswordCommandHandler $handler
    ): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true);
            $response = $handler->handle(
                new ChangePassword(
                    $body['id'],
                    $body['password'],
                    $body['verificationCode']),
                AccountRole::USER
            );
            return $response->toJsonResponse();
        }
        catch (Exception $exception)
        {
            return ApiResponse::error('Nothing Found', $exception, 400)->toJsonResponse();
        }
    }


    #[IsGranted(AccountRole::USER->value)]
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


    #[IsGranted(AccountRole::USER->value)]
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