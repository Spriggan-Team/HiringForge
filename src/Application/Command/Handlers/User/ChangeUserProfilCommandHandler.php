<?php

namespace App\Application\Command\Handlers\User;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\User\UserModifier;
use App\Application\DTO\User\ChangeUserProfileCommand;
use App\Infrastructure\Storage\FileStorage\FileUtils;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ChangeUserProfilCommandHandler
{
    public function __construct(
        private FileUtils $fileUtils,
        private UserModifier $modifier
    ){}

    public function handle(ChangeUserProfileCommand $command):array
    {
        try
        {
            //Check for "presentation" technique validation
            if( $command->presentation &&
                !$this->fileUtils->isTimedMedia($command->presentation)
            ){
                return ApiResponseBuilder::error(
                    "You must send timed media as the presentation field in your request",
                    new BadRequestException("[TimeMedia] Bad field validation")
                );
            }

            //Enforces that all the images data are real images and do not concern other data type  
            foreach($command->addImages as $image){
                if($this->fileUtils->isTimedMedia($image)){
                    return ApiResponseBuilder::error(
                        "Please, make sure your new images field is filled with only image files", 
                        new BadRequestException("[TimeMedia] Bad field validation")
                    );
                }
            }

            //Call for the usecase
            $this->modifier->execute(
                uuid: $command->uuid,
                name: $command->uuid,
                siret: $command->siret,
                addImages: $command->addImages,
                deleteImages: $command->deleteImages,
                presentation: null
            );
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        //...Fallback
        catch (\Throwable $th)
        {
            return ApiResponseBuilder::error("Something bad happened along the way", $th);
        }
    }
}