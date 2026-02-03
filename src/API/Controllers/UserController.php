<?php

namespace App\Api\Controllers;


use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\DTO\ChangePassword;
use App\Application\DTO\User\ChangeUserProfileCommand;

use App\Application\Command\Handlers\User\DeleteUserCommandHandler;
use App\Application\Command\Handlers\User\ResetPasswordCommandHandler;
use App\Application\Command\Handlers\User\SendVerificationCodeCommandHandler;

use App\Application\Query\Handlers\User\GetUserQueryHandler;

use App\Infrastructure\Security\UnauthorizedAction;
use App\Infrastructure\Security\UserGuard;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class UserController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
        private UserGuard $userguard
    ) {
        ApiResponseBuilder::init($logger);
    }

    #[Route("/users", methods: ["GET"], name: "fetch_user")]
    public function getAccountById(
        Request $request,
        GetUserQueryHandler $handler
    ): JsonResponse
    {
        try {
            $uuid = $this->userguard
                         ->assertAuthorization($request->headers->get("Authorization", null));
            $response = $handler->handle($uuid);
            return $this->json($response, 200);
        }
        catch(UnauthorizedAction $unauthorizedAction)
        {
            return $this->json(ApiResponseBuilder::error('Please, try connecting before', $unauthorizedAction), 401);
        }
        catch (Exception $exception) {
            return $this->json(ApiResponseBuilder::error('User not found', $exception), 404);
        }
    }



    #[Route("/user/password/verificationcode", methods:["PUT"], name: "update_account")]
    public function overwriteAccount(
        Request $request,
        SendVerificationCodeCommandHandler $handler
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $response = $handler->handle($data['email']);
            return $this->json($response, 200);
        }
        catch (Exception $exception) {
            $this->logger->error("Caught Exception: ". $exception->getMessage(), ['exception'=>$exception]);
            return $this->json(ApiResponseBuilder::error('Nothing Found'), 404);
        }
    }

    
    #[Route("/user/resetpassword", methods:["PATCH"], name: "reset_password")]
    public function updateUser(
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
                    $body['verificationCode'])
            );
            return $this->json($response, 200);
        }
        catch (Exception $exception)
        {
            return $this->json(ApiResponseBuilder::error('Nothing Found', $exception), 404);
        }
    }


    #[Route("/user/change", methods: ['PUT'], name: "change_user_data")]
    public function modify(
        Request $request,
        ChangeUserProfileCommand $handler
    )
    {
        try
        {
            $command = new ChangeUserProfileCommand();
        }
        catch(\Exception $excption)
        {

        }
    }

    
    #[Route('/delete/{uuid}', methods: ['DELETE'], name: "delete_account")]
    public function deleteAccount(
        string $uuid,
        Request $request,
        DeleteUserCommandHandler $handler
    ): JsonResponse
    {
        try
        {
            $uuid = $this->userguard
                         ->assertAuthorization($request->headers->get("Authorization", null));
            $response = $handler->handle($uuid);
            return $this->json($response, 200);
        }
        catch(UnauthorizedAction $unauthorizedAction)
        {
            return $this->json(ApiResponseBuilder::error('Please, try connecting before', $unauthorizedAction), 401);
        }
        catch (Exception $exception) {
            return $this->json(ApiResponseBuilder::error('Nothing Found',$exception), 404);
        }
    }

}

?>