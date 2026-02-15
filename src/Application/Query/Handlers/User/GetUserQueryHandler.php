<?php

namespace App\Application\Query\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponse;
use App\Application\Usecases\User\FetchUser;
use App\Domain\Exception\RessourceNotFound;
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
    public function handle(string $uuid): ApiResponse
    {
        try{
            //
            $user = $this->picker->execute($uuid);
            return ApiResponse::success($user, "Everything went smoothly");
        }
        catch(RessourceNotFound $exception){
            return ApiResponse::error("Something wrong happened",$exception);
        }
    }
}