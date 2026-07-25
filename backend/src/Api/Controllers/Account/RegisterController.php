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
use App\Domain\ApplicationErrorCode;
use App\Domain\Exception\CompanyAlreadyRegistered;

use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\Exception\FileSizeExceeded;
use App\Domain\Exception\FileTimeExceeded;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\ResourceCreationRejected;


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
                description: $inputBag->get("description", null),
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
        catch(EmailAlreadyRegistered $thr){
            $err = ApiResponse::error(
                message: "Account already registered",
                throwable: $thr,
                code: ApplicationErrorCode::ACCOUNT_ALREADY_EXISTS,
            );
            return $err->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error(message: "Something wen wrong", throwable: $exception)->toJsonResponse();
        }
    }



    #[Route("/user/register", methods: ["POST"], name: "user_register")]
    public function userRegister(
        Request $request,
        UserRegisterUseCase $usecase,
    ): JsonResponse
    {
        try {
            /** @var InputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterUserCommand(
                firstName: trim($inputBag->get("firstName")),
                lastName: trim($inputBag->get("lastName")),
                companyName: trim($inputBag->get('companyName')),
                email: trim($inputBag->get('email')),
                siret: trim($inputBag->get('siret')),
                password: $inputBag->get('password'),
                images: $request->files->get('images', []), //-- company images
                profileImage: $request->files->get("profileImage", null), 
                videoPresentation: $request->files->get('videoPresentation',null),
                description: trim($inputBag->get("description", null)),
                logo: $request->files->get("logo"),
                verificationCode: trim( $inputBag->get("verificationCode", null)),
                address:  Address::create(
                    street: trim($inputBag->get("street")),
                    postalCode: trim($inputBag->get("postalCode")),
                    country: trim($inputBag->get("country")),
                )
            );

            $result = $usecase->execute($command);

            return ApiResponse::success($result)->toJsonResponse();
        }
        //-- account error fallback
        catch(EmailAlreadyRegistered $emailException){
            return ApiResponse::error(
                message: 'This email is already registered', 
                throwable: $emailException,
                code: ApplicationErrorCode::ACCOUNT_ALREADY_EXISTS
            )->toJsonResponse();
        }
        catch(CompanyAlreadyRegistered $companyAlreadyRegistered){
            return ApiResponse::error(
                message: 'This company is already registered', 
                throwable: $companyAlreadyRegistered,
                code: ApplicationErrorCode::COMPANY_ALREADY_REGISTERED
            )->toJsonResponse();
        }
        //-- Otp error fallback
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
                code: $code,
            )->toJsonResponse();
        }
        //-- File error fallback
        catch(FileSizeExceeded $filesizeError){
            return ApiResponse::error(
                message: 'This email is already registered', 
                throwable: $filesizeError,
                code: ApplicationErrorCode::FILE_SIZE_EXCEEDED,
                data: $filesizeError->getPayload() ?? []
            )->toJsonResponse();
        }
        catch(FileTimeExceeded $filetimeError){
            return ApiResponse::error(
                message: 'This email is already registered', 
                throwable: $filetimeError,
                code: ApplicationErrorCode::FILE_TIME_EXCEEDED,
                data: $filetimeError->getPayload() ?? [],
            )->toJsonResponse();
        }
        //-- creation rejected
        catch(ResourceCreationRejected $ressourceCreation){
            return ApiResponse::error(
                message: "Failed to create user",
                throwable: $ressourceCreation,
                code: ApplicationErrorCode::RESSOURCE_CREATION_FAILED
            )->toJsonResponse();
        }
        //-- catch remaining all exception
        catch (\Exception $exception)
        {
            return ApiResponse::error(message: 'Please check your data fields and formats', throwable: $exception)->toJsonResponse();
        }
    }

}