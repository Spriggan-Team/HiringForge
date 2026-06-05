<?php

namespace App\Application\Usecases\User;

use App\Application\DTO\User\RegisterUserCommand;
use App\Application\Usecases\Account\AccountRegister; 

use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\FileSizeExceeded;
use App\Domain\Exception\FileTimeExceeded;
use App\Domain\Exception\RessourceNotFound;

use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;

use App\Domain\OTP\Exception\OTPException;
use App\Domain\OTP\OTPRepositoryInterface;

use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;
use App\Domain\User\Siret;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;



class UserRegisterUseCase
{
    public function __construct(
        private MediaFactoryInterface $mediaFactory,
        private MediaStorageInterface $storage,
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private OTPRepositoryInterface $OTPRepository
    ){}
    
    /**
     * @throws \DomainException|\Exception|OTPException|EmailAlreadyRegistered|OTPException
     */
    public function execute(RegisterUserCommand $command): AccountRegister
    {    
        $email = EmailAddress::create($command->email);
        
        try {
            $existingUser = $this->repository->assertExist(email: $email->value());
            if ($existingUser) {
                throw new EmailAlreadyRegistered("This user already exists"); 
            }
        } catch (\Exception $exception) {
            //silence error
        }

        $userId = UserId::create();

        /** @var string[] filenames array that have failed échoué */
        $filesFailedSize = [];
        $filesFailedTimeout = [];
        $filesFailedGeneric = []; 

        // -- Check verification code
        try {
            $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                $email->value(), 
                AccountFlowPurpose::SIGN_UP
            );
            
            $otp->verify($command->verificationCode, $this->hasher);
        }
        catch (RessourceNotFound) {
            throw new OTPException(message: "No verification code found for this account.", isInvalid: true);
        }
        catch (OTPException $e) {
            if (isset($otp)) {
                try {
                    $this->OTPRepository->update($email->value(), $otp);
                } catch (RessourceNotFound $exception) {
                    throw new OTPException(
                        message: "The verification session has expired or does not exist.", 
                        isInvalid: true,
                        previous: $exception
                    );
                }
            }
            
            //-- rethrow the exception to halt registration execution!
            throw $e;
        }



        // -- Create user
        $user = User::create(
            userId: $userId,
            name: $command->name,
            email: $email,
            passwordHash: $this->hasher->hash((new PlainPassword($command->password))->value()),
            siret: Siret::create($command->siret),
            address: $command->address,
        );

        // -- Upload video
        if ($command->videoPresentation) {
            try {
                $timedMedia = $this->mediaFactory->createTimedMedia($command->videoPresentation);
                $user->addVideoPresentation($timedMedia);
                
                $this->storage->store(
                    file: $command->videoPresentation,
                    ownerId: $userId->value(),
                    ownerType: MediaOwnerType::USER,
                    mediaPurpose: MediaPurpose::PROFILE,
                    errorCallback: function($result) use (&$user, &$filesFailedGeneric) {
                        $filesFailedGeneric[] = $result->originalName;
                        $user->removeVideoPresentation();
                    },
                );
            } catch (FileSizeExceeded $e) {
                $filesFailedSize[] = $e->getPayload()["originalName"];
            } catch (FileTimeExceeded $e) {
                $filesFailedTimeout[] = $e->getPayload()["originalName"];
            }
        }

        // -- Upload images
        foreach ($command->images as $uploadedImage) {
            try {
                $staticMedia = $this->mediaFactory->createStaticMedia($uploadedImage);
                $user->addImages($staticMedia);
                
                $this->storage->store(
                    file: $uploadedImage,
                    ownerId: $userId->value(),
                    storedFileName: $staticMedia->name,
                    ownerType: MediaOwnerType::USER,
                    mediaPurpose: MediaPurpose::PROFILE,
                    errorCallback: function($result) use (&$user, &$filesFailedGeneric, $staticMedia) {
                        $filesFailedGeneric[] = $result->originalName;
                        $user->removeImage($staticMedia);
                    }
                );
            } catch (FileSizeExceeded $e) {
               $filesFailedSize[] = $e->getPayload()["originalName"];
            } catch (FileTimeExceeded $e) {
                $filesFailedTimeout[] = $e->getPayload()["originalName"];
            }
        }
        
        // -- Save user
        $this->repository->save($user);
        
        //-- dto
        return new AccountRegister(
            userId: $userId->value(), 
            filesFailedGeneric: $filesFailedGeneric,
            filesFailedTimeout: $filesFailedTimeout,
            filesFailedSize: $filesFailedSize
        );
    }
}