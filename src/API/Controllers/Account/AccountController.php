<?php

namespace App\Api\Controllers\Account;

use App\Api\Responder\ApiResponse;

use App\Application\DTO\ChangeEmail;
use App\Application\DTO\ChangePassword;
use App\Domain\Shared\Account\AccountRole;

use App\Application\Command\Handlers\Account\ChangeAccountEmailCommandHandler;
use App\Application\Command\Handlers\Account\ResetPasswordCommandHandler;

use App\Application\Command\Handlers\Account\SendVerificationCodeCommandHandler;
use App\Domain\Shared\Account\AccountFlowPurpose;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/account')]
class AccountController extends AbstractController
{
    /**
     * Sends a password reset verification code to the user.
     */
    #[Route("/verificationcode", methods:["GET"], name: "update_account")]
    public function sendPasswordOTP(
        Request $request,
        SendVerificationCodeCommandHandler $handler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $accountRole = AccountRole::tryFrom($data['origin']);
            $purpose = AccountFlowPurpose::from($data['purpose']);
            if($purpose)
            {
                $response = $handler->handle(
                    $data['email'],
                    $purpose,
                    $accountRole
                );
                return $response->toJsonResponse();
            }
            return ApiResponse::error("Missing or invalid purpose field")->toJsonResponse();
        }
        catch (Exception $exception) {
            return ApiResponse::error(
                message:'Nothing Found',
                statusCode: 400, throwable: $exception
            )->toJsonResponse();
        }
    }

    
    /**
     * Resets the user password after successful OTP validation.
     */
    #[Route("/resetpassword", methods:["PATCH"], name: "reset_password")]
    public function resetPassword(
        Request $request,
        ResetPasswordCommandHandler $handler
    ): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true);
            $accountRole  = AccountRole::fromString($body['origin']);
            if($accountRole){
                $response = $handler->handle(
                    new ChangePassword(
                        $body['email'],
                        $body['password'],
                        $body['verificationCode']),
                    $accountRole
                );
                return $response->toJsonResponse();
            }
            return ApiResponse::error("Missing or Invalid origin field")->toJsonResponse();
        }
        catch (Exception $exception)
        {
            return ApiResponse::error('Nothing Found', $exception, 400)->toJsonResponse();
        }
    }

    /**
     * This one allow a user to change its email;
     * The requirements here, are to submit the old email along side the new one.
     * You must provide the password and be authentificated here too, because just the authentification
     * is not secure enough if the user momentary/temporary lost its devices (and as for other concerns)...
     */
    #[Route('/change/email', methods: ['PATCH'], name: "")]
    public function changeEmail(
        Request $request,
        ChangeAccountEmailCommandHandler $handler
    )
    {
        try
        {
            $body = json_decode($request->getContent());
            $command = new ChangeEmail(
                oldEMail: $body['oldEmail'],
                newEmail: $body['newEmail'],
                password: $body['password'],
                verificationCode: $body['verificationCode'],
            );
            $accountRole  = AccountRole::from($body['origin']);
            $response = $handler->handle($command, $accountRole);
            return $response->toJsonResponse();
        }
        catch(\Throwable $th)
        {
            return ApiResponse::error('User not found', $th, 404)->toJsonResponse();
        }
    }


}