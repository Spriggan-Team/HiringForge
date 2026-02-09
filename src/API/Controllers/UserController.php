<?php

namespace App\Api\Controllers;


use Exception;

use App\Domain\Shared\Address;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Handlers\User\ChangeUserEmailCommandHandler;
use App\Application\Command\Handlers\User\ChangeUserProfilCommandHandler;

use App\Application\DTO\ChangeEmail;
use App\Application\DTO\ChangePassword;
use App\Application\DTO\User\ChangeUserProfileCommand;

use App\Application\Command\Handlers\User\DeleteUserCommandHandler;
use App\Application\Command\Handlers\User\ResetPasswordCommandHandler;
use App\Application\Command\Handlers\User\SendVerificationCodeCommandHandler;
use App\Application\Query\Handlers\User\GetUserQueryHandler;
use App\Infrastructure\Security\UserGuard;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class UserController extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger,
        private UserGuard $userguard
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponseBuilder::init($logger);
    }


    #[Route("/users", methods: ["GET"], name: "fetch_user")]
    public function getUserById(
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
        catch (Exception $exception) {
            return $this->json(ApiResponseBuilder::error('User not found', $exception), 404);
        }
    }


    #[Route('/user/change/email', methods: ['PATCH'], name: "")]
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

            $reponse = $handler->handle($command);
            return $this->json($reponse, Response::HTTP_OK);
        }
        catch(\Throwable $th)
        {
            return $this->json(ApiResponseBuilder::error('User not found', $th), 404);
        }
    }



    #[Route("/user/password/verificationcode", methods:["PUT"], name: "update_account")]
    public function sendVerificationCode(
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
                    $body['verificationCode'])
            );
            return $this->json($response, 200);
        }
        catch (Exception $exception)
        {
            return $this->json(ApiResponseBuilder::error('Nothing Found', $exception), 404);
        }
    }



    #[Route("/user/change", methods: ['PATCH'], name: "change_user_data")]
    public function modify(
        Request $request,
        ChangeUserProfilCommandHandler $handler
    ): JsonResponse
    {
        try
        {
            $body = json_decode($request->getContent());
            $uuid = $this->userguard
                         ->assertAuthorization($request->headers->get("Authorization", null));
            $command = new ChangeUserProfileCommand(
                uuid: $uuid,
                name: $body['name'],
                siret: $body['siret'],
                addImages: $body['images']['add'] ?? [],
                deleteImages: $body['images']['delete'] ?? [],
                presentation: $body['presentation'],
                address: new Address(
                    street:     $body['street'],
                    city:       $body['city'],
                    postalCode: $body['postalCode'],
                    country:    $body['country']
                )
            );
            $response = $handler->handle($command);
            return $this->json($response, 200);
        }
        catch(\Exception $exception)
        {
            return $this->json(ApiResponseBuilder::error('Nothing Found',$exception), 404);
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
        catch (Exception $exception) {
            return $this->json(ApiResponseBuilder::error('Nothing Found',$exception), 404);
        }
    }

}

?>