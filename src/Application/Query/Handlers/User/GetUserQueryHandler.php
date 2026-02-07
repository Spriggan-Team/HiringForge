<?php

namespace App\Application\Query\Handlers\User;

use Exception;

use App\Domain\User\UserId;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Query\Usecase\User\FetchUser;
use App\Domain\Exception\RessourceNotFound;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class GetUserQueryHandler
{

    public function __construct(
        private FetchUser $picker,
        private ValidatorInterface $validator
    ){}

    /**
     * This handler enforce technique validation for users data retreiving
     * and call for the associated usecase
     */
    public function handle(string $uuid): array
    {
        try{
            //
            $user = $this->picker->execute($uuid);
            return ApiResponseBuilder::success($user, "Everything went smoothly");
        }
        catch(RessourceNotFound $exception){
            return ApiResponseBuilder::error("Something wrong happened",$exception);
        }
    }
}