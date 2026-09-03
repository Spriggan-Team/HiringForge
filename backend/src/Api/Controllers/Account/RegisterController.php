<?php

namespace App\Api\Controllers\Account;

use App\Api\Controllers\Account\Mapper\RegisterCandidateCommandMapper;
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


    #[Route('/candidate/register', methods: ["POST"], name: 'candidate_register')]
    public function candidateRegister(
        Request $request,
        CandidateRegisterUsecase $usecase,
        RegisterCandidateCommandMapper $mapper
    ): JsonResponse
    {
        try{
            $command = $mapper->map(
                form: $request->request,
                files: $request->files
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
                message: 'One of the file exceed the authorized size', 
                throwable: $filesizeError,
                code: ApplicationErrorCode::FILE_SIZE_EXCEEDED,
                data: $filesizeError->getPayload() ?? []
            )->toJsonResponse();
        }
        catch(FileTimeExceeded $filetimeError){
            return ApiResponse::error(
                message: 'One of the file (video) exceed the authorierd duration', 
                throwable: $filetimeError,
                code: ApplicationErrorCode::FILE_TIME_EXCEEDED,
                data: $filetimeError->getPayload() ?? [],
            )->toJsonResponse();
        }
        //-- creation rejected
        catch(ResourceCreationRejected $ressourceCreation){
            return ApiResponse::error(
                message: "Failed to create candidate",
                throwable: $ressourceCreation,
                code: ApplicationErrorCode::RESSOURCE_CREATION_FAILED
            )->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error(
                message: "Something wen wrong",
                throwable: $exception
            )->toJsonResponse();
        }
    }



    #[Route("/user/register", methods: ["POST"], name: "user_register")]
    public function userRegister(
        Request $request,
        UserRegisterUseCase $usecase,
    ): JsonResponse
    {
        try {
            /** @var \Symfony\Component\HttpFoundation\InputBag $inputBag FormData stored in request by Symfony  */
            $inputBag = $request->request;

            $command = new RegisterUserCommand(
                firstName: $this->getString($inputBag, "firstName"),
                lastName: $this->getString($inputBag, "lastName"),
                companyName: $this->getString($inputBag, 'companyName'),
                email: $this->getString($inputBag, 'email'),
                siret: $this->getString($inputBag,'siret'),
                password: $this->getString($inputBag, 'password'),
                images: $request->files->get('images', []), //-- company images
                profileImage: $request->files->get("profileImage", null), 
                videoPresentation: $request->files->get('videoPresentation',null),
                description: $this->getNullableString($inputBag, "description"),
                logo: $request->files->get("logo"),
                verificationCode: $this->getString( $inputBag, "verificationCode"),
                address:  Address::create(
                    city: $this->getString($inputBag, "city"),
                    street: $this->getString($inputBag, "street"),
                    postalCode: $this->getString($inputBag, "postalCode"),
                    country: $this->getString($inputBag, "country"),
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
                message: 'One of the file exceed the authorized size', 
                throwable: $filesizeError,
                code: ApplicationErrorCode::FILE_SIZE_EXCEEDED,
                data: $filesizeError->getPayload() ?? []
            )->toJsonResponse();
        }
        catch(FileTimeExceeded $filetimeError){
            return ApiResponse::error(
                message: 'One of the file (video) exceed the authorierd duration', 
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


    private function getString(
        \Symfony\Component\HttpFoundation\InputBag $input,
        string $key
    ): string {
        return trim((string) $input->get($key, ''));
    }


    private function getNullableString(
        \Symfony\Component\HttpFoundation\InputBag $input,
        string $key
    ): ?string {
        $value = $input->get($key);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}