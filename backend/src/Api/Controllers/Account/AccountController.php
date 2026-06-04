<?php

namespace App\Api\Controllers\Account;

use App\Api\Responder\ApiResponse;

use App\Application\DTO\ChangeEmail;
use App\Application\DTO\ChangePassword;


use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Application\Usecases\Account\AccountEmailRenitializer;
use App\Application\Usecases\Account\AccountPasswordRenitializer;
use App\Application\Usecases\Account\VerificationCodeSender;
use App\Domain\ApplicationErrorCode;
use App\Domain\Exception\EmailAlreadyRegistered
;
use Exception;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/account')]
class AccountController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponse::init($logger);
    }


    /**
     * Sends a password reset verification code to the user.
     */
    #[Route("/verificationcode", methods:["POST"], name: "update_account")]
    public function sendPasswordOTP(
        Request $request,
        VerificationCodeSender $usecase
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return ApiResponse::error(
                    'Invalid JSON payload',
                    statusCode: 400
                )->toJsonResponse();
            }

            if (!isset($data['email'], $data['purpose'])) {
                return ApiResponse::error(
                    'Missing email or purpose field',
                    statusCode: 400
                )->toJsonResponse();
            }

            $purpose = AccountFlowPurpose::from($data['purpose']);

            $usecase->execute(
                email: $data['email'],
                purpose: $purpose,
            );

            return ApiResponse::notice(
                'Everything went smoothly'
            )->toJsonResponse();
        }
        catch(EmailAlreadyRegistered $emailAlreadyExist){
            $res = ApiResponse::error(
                message: "This email is already used. Please try with another one",
                code: ApplicationErrorCode::ACCOUNT_ALREADY_EXISTS,
                throwable: $emailAlreadyExist
            );
            return $res->toJsonResponse();
        }
        catch (Exception $exception) {
            $res = ApiResponse::error(
                message:'Nothing Found',
                statusCode: 400, throwable: $exception
            );
            return $res->toJsonResponse();
        }
    }

    
    /**
     * Resets the user password after successful OTP validation.
     */
    #[Route("/resetpassword", methods:["PATCH"], name: "reset_password")]
    public function resetPassword(
        Request $request,
        AccountPasswordRenitializer $usecase
    ): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true);
            $command = new ChangePassword(
                    $body['email'],
                    $body['password'],
                    $body['verificationToken']);
            $usecase->execute($command);
            return ApiResponse::notice('Everything is ok')->toJsonResponse();
        }
        catch (Exception $exception)
        {
            return ApiResponse::error('Nothing Found', throwable: $exception, statusCode: 400)->toJsonResponse();
        }
    }

    /**
     * This one allow a user to change its email;
     * The requirements here, are to submit the old email along side the new one.
     * You must provide the password and be authentificated here too, because just the authentification
     * is not secure enough if the user momentary/temporary lost its credentials or devices (and as for other concerns)...
     */
    #[Route('/change/email', methods: ['PATCH'], name: "")]
    public function changeEmail(
        Request $request,
        AccountEmailRenitializer $usecase
    )
    {
        try
        {
            $body = json_decode($request->getContent());
            $command = new ChangeEmail(
                oldEmail: $body['oldEmail'],
                newEmail: $body['newEmail'],
                password: $body['password'],
                verificationToken: $body['verificationCode'],
            );
            $usecase->execute($command);
            return ApiResponse::notice("Your email has been updated")->toJsonResponse();
        }
        catch(\Throwable $th)
        {
            return ApiResponse::error('User not found', throwable: $th, statusCode: 404)->toJsonResponse();
        }
    }


}