<?php

namespace App\Application\Command\Handlers\Candidate;


use App\Api\Responder\ApiResponse;
use App\Api\DTO\Candidate\RegisterCandidateCommand;
use App\Application\Usecases\Candidate\CandidateRegisterUsecase;

use App\Domain\Exception\UnbaleToStoreFile;
use App\Domain\Exception\EmailAlreadyRegistered;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RegisterCandidateCommandHandler
{

    public function __construct(
        private CandidateRegisterUsecase $register,
        private ValidatorInterface $validator
    ){} 

    public function handle(RegisterCandidateCommand $request): ApiResponse
    {
        try{
            $erros = $this->validator->validate($request);
            if(count($erros) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }


            $register = $this->register->execute(
                firstName: $request->firstName,
                lastName: $request->lastName,
                email: $request->email,
                password: $request->password,
                uploadedImage: $request->image,
                uploadedCV: $request->cv,
                address: $request->address,
                searchRadius: $request->searchRadius
            );

            return ApiResponse::success(
                $register,
                "Everything went smoothly"
            );
        }
        //...Fallbacks
        catch(BadRequestHttpException $badRequestHttp)
        {
            return ApiResponse::error("Something went wrong, Please check the integrity of your data", throwable: $badRequestHttp);
        }
        catch(EmailAlreadyRegistered $emailAlreadyExist)
        {
            return ApiResponse::error("Please use a proper email", throwable: $emailAlreadyExist);
        }
        catch(UnbaleToStoreFile $stroreFailed)
        {
            return ApiResponse::error("Your cv has failed to be uploaded", throwable: $stroreFailed);
        }
    }
}