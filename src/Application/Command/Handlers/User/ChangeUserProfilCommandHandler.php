<?php

namespace App\Application\Command\Handlers\User;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\User\ChangeUserProfileCommand;
use App\Application\Command\Usecase\User\UserModifier;


use Symfony\Component\HttpFoundation\Exception\BadRequestException;


class ChangeUserProfilCommandHandler
{
    public function __construct(
        private UserModifier $modifier
    ){}

    public function handle(ChangeUserProfileCommand $command):ApiResponse
    {
        try
        {

            //Call for the usecase
            $failedUplods = $this->modifier->execute(
                uuid: $command->uuid,
                name: $command->uuid,
                siret: $command->siret,
                addImages: $command->addImages,
                deleteImages: $command->deleteImages,
                videoPresentation: $command->videoPresentation
            );

            return ApiResponse::success(
                data: ["failedUploading" => $failedUplods],
                message: "You request have reached the server and has been treated correctly!! If something uploads have failed please try again!!"
            );
        }
        //...Fallback
        catch (\Throwable $th)
        {
            throw $th;
        }
    }
}