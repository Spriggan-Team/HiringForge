<?php

namespace App\Api\Controllers\Account;

use App\Application\DTO\Candidate\RegisterCandidateCommand;
use App\Application\DTO\User\RegisterUserCommand;

use App\Domain\Shared\Address;
use App\Api\Responder\ApiResponse;
use App\Application\Usecases\Candidate\CandidateRegisterUsecase;
use App\Application\Usecases\User\UserRegisterUseCase;


use Exception;
use  Psr\Log\LoggerInterface;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\ApplicationErrorCode;
use App\Domain\OTP\Exception\OTPException;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class RegisterController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
        ApiResponse::init($this->logger);
    }


    #[Route('/candaidate/register', methods: ["POST"], name: 'candidate_register')]
    public function candidateRegister(
        Request $request,
        CandidateRegisterUsecase $usecase
    ): JsonResponse
    {
        try{
            /** @var InputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterCandidateCommand(
                lastName: $inputBag->get('firstName'),
                firstName: $inputBag->get('firstName'),
                email: $inputBag->get('email'),
                password: $inputBag->get('password'),
                image: $request->files->get('image', null),
                cv: $request->files->get('cv', null),
                address:  Address::tryCreate([
                    'street' => $inputBag->get("address[street]"),
                    'country' => $inputBag->get("address[country]"),
                    'postalCode' => $inputBag->get("address[postalCode]"),
                ]),
                searchRadius:  $inputBag->get("searchRadius")
            );
            $result = $usecase->execute($command);
            return ApiResponse::success($result)->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error(message: "Something wen wrong", throwable: $exception)->toJsonResponse();
        }
    }



    #[Route("/users/register", methods: ["POST"], name: "user_register")]
    public function userRegister(
        Request $request,
        UserRegisterUseCase $usecase,
    ): JsonResponse
    {
        try {
            /** @var InputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterUserCommand(
                name:  $inputBag->get('name'),
                email: $inputBag->get('email'),
                siret: $inputBag->get('siret'),
                password: $inputBag->get('password'),
                images: $request->files->get('images', []),
                videoPresentation: $request->files->get('videoPresentation',null),
                desc: $inputBag->get("description", null),
                logo: $request->files->get("logo"),
                verificationCode: $inputBag->get("verificationCode", null),
                address:  Address::create(
                    street: $inputBag->get("street"),
                    postalCode: $inputBag->get("postalCode"),
                    country: $inputBag->get("country"),
                )
            );
            $result = $usecase->execute($command);
            return ApiResponse::success($result)->toJsonResponse();
        }
        catch(EmailAlreadyRegistered $emailEception){
            return ApiResponse::error(
                message: 'This email is already registered', 
                throwable: $emailEception,
                code: ApplicationErrorCode::ACCOUNT_ALREADY_EXISTS
            )->toJsonResponse();
        }
        catch(OTPException $optError){
            $code = $optError->expired ? 
                    ApplicationErrorCode::EXPIRED_OTP 
                        : ( $optError->isInvalid 
                                ? ApplicationErrorCode::INVALID_OTP
                                : null
                            );
            return ApiResponse::error(
                message: "OTP code expired",
                throwable: $optError,
                statusCode: 410,
                code: $code
            )->toJsonResponse();
        }
        catch (\Exception $exception)
        {
            return ApiResponse::error(message: 'Please check your data fields and formats', throwable: $exception)->toJsonResponse();
        }
    }

}