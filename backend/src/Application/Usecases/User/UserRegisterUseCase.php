<?php


namespace App\Application\Usecases\User;

use App\Application\DTO\User\RegisterUserCommand;
use App\Domain\Exception\EmailAlreadyRegistered;

use App\Domain\User\User;
use App\Domain\User\Siret;
use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\File\MediaFactoryInterface;

use App\Domain\Shared\EmailAddress;

use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;

use App\Application\Usecases\Account\AccountRegister;


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
     * This is an usecase that enforce buisness requirement and then proceed with saving
     * @throws \DomainException|EmailAlreadyRegistered|RessourceNotFound|FileExceedTime|FileSizeExceeded What is thrown when requirements are not respected;
     *                                                                      (most of them are exception from /Domain)
     */
    public function execute(
        RegisterUserCommand $command
    ): AccountRegister
    {    
        $email =  EmailAddress::create($command->email);
        try{
            $existingUser = $this->repository->findByEmail($email->value());    //check for any existing user
            if($existingUser){
                throw new EmailAlreadyRegistered("This user already exist"); 
            }
        }
        catch(\Exception $excption){}


        $userId = UserId::create();

        /** @var string[]  the storedName of the sucessful uploading filename */
        $failedUploads = []; 

        //-- check verification code
        $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose($email->value(), AccountFlowPurpose::SIGN_UP);
        $isOtpVerified = $otp->verify($command->verificationCode, $this->hasher);

            //--Throws logic exception when hash verification not succeed
        if(!$isOtpVerified)
        {
            throw new \DomainException("OTP code not correct!!");
        }

        //-- Create user
        $user =  User::create(
            userId: $userId,
            name:   $command->name,
            email:  $email,
            passwordHash: $this->hasher->hash((new PlainPassword($command->password))->value()),
            siret:  Siret::create($command->siret),
            address: $command->address,
        );

        //-- Upload video
        if($command->videoPresentation){
            $timedMedia = $this->mediaFactory->createTimedMedia($command->videoPresentation);
            $user->addVideoPresentation($timedMedia);
            $this->storage->store(
                $command->videoPresentation,
                ownerId: $userId->value(),
                ownerType: MediaOwnerType::USER,
                mediaPurpose: MediaPurpose::PROFILE,
                errorCallback:  function($result) use(&$user, &$failedUploads)
                {
                    $failedUploads[] = $result->originalName;
                    $user->removeVideoPresentation();
                },
            );
        }

        //-- Upload images
        foreach($command->images as $uploadedImage)
        {
           $staticMedia = $this->mediaFactory->createStaticMedia($uploadedImage);
           $this->storage->store(
                    $uploadedImage,
                    $userId->value(),
                    storedFileName: $staticMedia->name,
                    ownerType: MediaOwnerType::USER,
                    mediaPurpose: MediaPurpose::PROFILE,
                    successCallback: function() use (&$user, &$staticMedia) {
                        $user->addImages($staticMedia);
                    },
                    errorCallback: function($result) use (&$user, &$failedUploads, $staticMedia) {
                        $failedUploads[] = $result->originalName;
                        $user->removeImage($staticMedia);
                    }
            );
        }
        
        //-- save user
        $this->repository->save($user);
        
        return new AccountRegister(
            $userId->value(), 
            $failedUploads
        );
    }
}