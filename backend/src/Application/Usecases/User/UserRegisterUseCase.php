<?php

namespace App\Application\Usecases\User;

use App\Application\DTO\User\RegisterUserCommand;
use App\Application\Usecases\Account\AccountRegister;

use App\Domain\Company\Company;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Exception\CompanyAlreadyRegistered;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\FileSizeExceeded;
use App\Domain\Exception\FileTimeExceeded;
use App\Domain\Exception\ResourceCreationRejected;
use App\Domain\Exception\ResourceNotFoundException;

use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageInterface;
use App\Domain\Department\DepartmentInitializerService;
use App\Domain\File\MediaStorageParams;
use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\OTP\OTPRepositoryInterface;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Account\AccountId;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\CustomUUID;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;
use App\Domain\Shared\TransactionManagerInterface;

use App\Domain\User\Siret;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\UserRole;



class UserRegisterUseCase
{
    public function __construct(
        private MediaFactoryInterface $mediaFactory,
        private MediaStorageInterface $storage,
        private UserRepositoryInterface $userRepository,
        private CompanyRepositoryInterface $companyRepository,
        private PasswordHasherInterface $hasher,
        private OTPRepositoryInterface $OTPRepository,
        private TransactionManagerInterface $transactionManager,
        private DepartmentInitializerService $departmentInitializer
    ){}
    
    /**
     * @throws \DomainException|\Exception|OTPException|EmailAlreadyRegistered|ResourceCreationRejected
     */
    public function execute(RegisterUserCommand $command): AccountRegister
    {    
        $email = EmailAddress::create($command->email);
        
        // -- Check if email already exists
        try{
            $identity = $this->userRepository->assertExist(email: $email->value());
            if($identity){
                throw new EmailAlreadyRegistered("This user already exists"); 
            }
        }
        catch(ResourceNotFoundException){}

        $userId = AccountId::create();

        /** @var string[] filenames array that have failed */
        $filesFailedSize = [];  //-- Files with size error
        $filesFailedTimeout = []; //-- File with durout error
        $filesFailedGeneric = []; //-- Files with other error
        $successfulUploads = [];  //-- Succes files - names

        /** @var array<int, MediaStorageParams> */
        $successfulUploasParams = []; //-- Falbacl for clearing invalid saved file (bdd insertion failed)

        // -- Check verification code
        try {
            $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                $email->value(), 
                AccountFlowPurpose::SIGN_UP
            );
            
            $isValid = $otp->verify($command->verificationCode, $this->hasher);
            if(!$isValid){
                throw new OTPException(message: "OTP verification invalid", isInvalid: true);
            }
        }
        catch (ResourceNotFoundException) {
            throw new OTPException(message: "No verification code found for this account.", isInvalid: true);
        }
        catch (OTPException $e) {
            if (isset($otp)) {
                try {
                    $this->OTPRepository->update($email->value(), $otp);
                } catch (ResourceNotFoundException $exception) {
                    throw new OTPException(
                        message: "The verification session has expired or does not exist.", 
                        isInvalid: true,
                        previous: $exception
                    );
                }
            }
            throw $e;
        }

        // -- Create Company
        if($this->companyRepository->exists($command->companyName)){
            throw new CompanyAlreadyRegistered("Company already registered");
        }


        $company = Company::create(
            id: CustomUUID::generate(),
            name: $command->companyName,
            siret: Siret::create($command->siret),
            address: [$command->address],
        );

        // -- Create User with Admin Role
        $user = User::create(
            userId: $userId->value(),
            email: $email,
            lastName: $command->lastName,
            firstName: $command->firstName,
            description: $command->description,
            passwordHash: $this->hasher->hash((new PlainPassword($command->password))->value()),
            role: UserRole::COMPANY_ADMIN, //-- Is admin by default
            companyId: $company->id()
        );


        // -- Upload video (Company - presentation vidéo)
        if ($command->videoPresentation) {
            try {
                $timedMedia = $this->mediaFactory->createTimedMedia($command->videoPresentation);
                $company->addVideoPresentation($timedMedia);
                
                $params = AccountStorageParams::companyVideoPresentation(
                    companyId: $company->id(),
                );
                $this->storage->store(
                    file: $command->videoPresentation,
                    params: $params,
                    storedFileName: $timedMedia->name,      
                    errorCallback: function($result) use (&$company, &$filesFailedGeneric) {
                        $filesFailedGeneric[] = $result->originalName;
                        $company->removeVideoPresentation();
                    },
                    successCallback: function ()use(&$params, &$successfulUploasParams){
                        $successfulUploasParams[] = $params;
                    }
                );
            }
            catch (FileSizeExceeded $e) {
                $filesFailedSize[] = $e->getPayload()["originalName"];
            }
            catch (FileTimeExceeded $e) {
                $filesFailedTimeout[] = $e->getPayload()["originalName"];
            }
        }

        // -- Method for handling single upload (static media)
        $uploadMedia = function($file, MediaStorageParams $params, callable $onAttach, callable $onDetach) 
            use (&$filesFailedGeneric, &$filesFailedSize, &$successfulUploads, $user, &$successfulUploasParams) {
                try {
                    $staticMedia = $this->mediaFactory->createStaticMedia($file);
                    
                    $onAttach($staticMedia);
                    $this->storage->store(
                        file: $file,
                        params: $params,
                        storedFileName: $staticMedia->name,
                        errorCallback: function($result) use (&$filesFailedGeneric, $onDetach, $staticMedia) {
                            $filesFailedGeneric[] = $result->originalName;
                            $onDetach($staticMedia);
                        },
                        successCallback:  function($result) use (
                            &$successfulUploads,
                            $params,
                            &$successfulUploasParams
                        ) {
                            $successfulUploads[] = $result->storedName;
                            $successfulUploasParams[] = $params;
                        }
                    );
                }
                catch (FileSizeExceeded $e) {
                    $filesFailedSize[] = $e->getPayload()["originalName"];
                }
            };

        // --- Treatment of logo
        if ($command->logo) {
            $uploadMedia(
                file: $command->logo,
                params: AccountStorageParams::companyLogo(companyId: $company->id()),
                onAttach: fn($media) => $company->setLogo($media),
                onDetach: fn($media) => $company->removeImage($media)
            );
        }

        // -- User Profile Image (Correction appliquée)
        if ($command->profileImage) {
            $uploadMedia(
                file: $command->profileImage,
                params: AccountStorageParams::recruiterProfile(companyId: $company->id()),
                onAttach: fn($media) => $user->addImage($media),
                onDetach: fn($media) => $user->removeImage()
            );
        }

        // --- Treatment of Images (Company images)
        foreach ($command->images as $uploadedImage) {
            $uploadMedia(
                file: $uploadedImage,
                params: AccountStorageParams::companyImages(companyId: $company->id()),
                onAttach: fn($media) => $company->addImages($media),
                onDetach: fn($media) => $company->removeImage($media)
            );
        }

        // -- Save elements
        try {
           $this->transactionManager->execute(function() use (&$company, &$user){
                $this->companyRepository->save($company);
                $this->userRepository->save($user);
                $this->departmentInitializer->initForCompany($company);
           });
        }
        catch (ResourceCreationRejected $e) {
            // Rollback files
            foreach ($successfulUploasParams as $param) {
                try {
                    $this->storage->remove(
                        params: $param,
                        recursive: true
                    );
                }
                catch (\Exception $storageException) {}
            }

            throw $e;
        }

        //-- Returned Value
        
        return new AccountRegister(
            filesFailedGeneric: $filesFailedGeneric,
            filesFailedTimeout: $filesFailedTimeout,
            filesFailedSize: $filesFailedSize,
            successfulUploads: $successfulUploads
        );
    }
}