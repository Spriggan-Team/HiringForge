<?php

namespace App\Application\Command\Handlers\Candidate;


use App\Api\Responder\ApiResponseBuilder;
use App\Api\DTO\Candidate\RegisterCandidateCommand;
use App\Application\Command\Usecase\Candidate\CandidateRegister;

use App\Application\Serializer\ActorView;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\UnbaleToStoreFile;
use App\Infrastructure\Storage\FileStorage\FileUtils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterCandidateCommandHandler
{

    public function __construct(
        private ActorView $viewer,
        private FileUtils $fileUtils,
        private CandidateRegister $register,
        private ValidatorInterface $validator
    ){} 

    public function handle(RegisterCandidateCommand $request):array
    {
        try{
            $erros = $this->validator->validate($request);
            if(count($erros) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $cv = $this->fileUtils->parseAsStaticMedia($request->cv);
            $image = $this->fileUtils->parseAsStaticMedia($request->image);
            //construct necessary value object here...

            $register = $this->register->execute(
                firstName: $request->firstName,
                lastName: $request->lastName,
                email: $request->email,
                password: $request->password,
                cv: $cv,
                image: $image
            );
            return ApiResponseBuilder::success(
                $this->viewer->register($register->id, $register->failedImages),
                "Everything went smoothly"
            );
        }
        //...Fallbacks
        catch(BadRequestHttpException $badRequestHttp)
        {
            return ApiResponseBuilder::error("Something went wrong, Please check the integrity of your data", $badRequestHttp);
        }
        catch(EmailAlreadyRegistered $emailAlreadyExist)
        {
            return ApiResponseBuilder::error("Please use a proper email", $emailAlreadyExist);
        }
        catch(UnbaleToStoreFile $stroreFailed)
        {
            return ApiResponseBuilder::error("Your cv has failed to be uploaded", $stroreFailed);
        }
    }
}