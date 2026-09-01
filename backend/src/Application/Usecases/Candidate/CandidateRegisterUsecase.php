<?php

namespace App\Application\Usecases\Candidate;

use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateId;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;

use App\Application\Usecases\Account\AccountRegister;
use App\Application\DTO\Candidate\RegisterCandidateCommand;

use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageScope;

use App\Domain\OTP\Exceptions\OTPException;
use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\AccountStorageParams;


class CandidateRegisterUsecase
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CandidateRepositoryInterface $candidateRepository,
        private PasswordHasherInterface $hasher,
        private OTPRepositoryInterface $OTPRepository,
        private MediaStorageInterface $storage,
        private MediaFactoryInterface   $mediaFactory,
    ){}

  /**
     * @throws EmailAlreadyRegistered|ResourceNotFoundException|\Throwable
     * @return AccountRegister
     */
    public function execute(
        RegisterCandidateCommand $command
    ): AccountRegister {
        $email = EmailAddress::create($command->email);

        if ($this->accountRepository->exists(null, $email->value())) {
            throw new EmailAlreadyRegistered();
        }

        // -- Check verification code
        try {
            $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                $email->value(),
                AccountFlowPurpose::SIGN_UP
            );

            $otp->verify($command->verificationCode, $this->hasher);
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

        // -- Create User entity model
        $candidateId = CandidateId::create();
        $failedUploads = [];
        $successfullUploads = []; // Rollback storage

        $candidate = Candidate::create(
            id: $candidateId->value(),
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $email,
            passwordHash: $this->hasher->hash((new PlainPassword($command->password))->value()),
            address: $command->address,
            searchRadius: $command->searchRadius
        );

        // -- Images Types
        if ($command->image) {
            $staticImage = $this->mediaFactory->createStaticMedia($command->image, expectedTypes: ['image/jpeg', 'image/png', 'image/webp']);
            $candidate->addImage($staticImage);
            
            $params = AccountStorageParams::candidateProfileImage(
                candidateId: $candidateId->value(),
                storedFileName:  $staticImage->name,
            );
            $this->storage->store(
                file: $command->image,
                params: $params,
                errorCallback: function ($result) use (&$candidate, &$failedUploads) {
                    $failedUploads[] = $result->originalName;
                    $candidate->removeImage();
                },
                successCallback: function () use (&$successfullUploads, $staticImage, $candidateId) {
                    $successfullUploads[] = [
                        'storedFileName' => $staticImage->name,
                        'ownerId'        => $candidateId->value(),
                        'ownerType'      => MediaOwnerType::CANDIDATE,
                        'mediaPurpose'   => MediaPurpose::PROFILE,
                        'mime'           => $staticImage->mime
                    ];
                },
            );
        }

        // -- CV Resume
        if ($command->cv) {
            $staticCV = $this->mediaFactory->createStaticMedia(file: $command->cv, expectedTypes: ["application/pdf"]);
            $candidate->addCV($staticCV);
            $params = AccountStorageParams::resumes(
                candidateId: $candidateId->value(),
                storedFileName: $staticCV->name
            );

            $this->storage->store(
                file: $command->cv,
                params: $params,
                errorCallback: function ($result) use (&$candidate, &$failedUploads) {
                    $failedUploads[] = $result->originalName;
                    $candidate->removeCv();
                },
                successCallback: function () use (&$successfullUploads, $staticCV, $candidateId) {
                    $successfullUploads[] = [
                        'storedFileName' => $staticCV->name,
                        'ownerId'        => $candidateId->value(),
                        'ownerType'      => MediaOwnerType::CANDIDATE,
                        'mediaPurpose'   => MediaPurpose::CV,
                        'mime'           => $staticCV->mime
                    ];
                },
            );
        }

        // -- Persistence
        try {
            $this->candidateRepository->save($candidate);
        }
        catch (\Throwable $exception) {
            //Clear file
            foreach ($successfullUploads as $mediaPayload) {
                $params = AccountStorageParams::create(
                    ownerId: $mediaPayload['ownerId'],
                    purpose: $mediaPayload['mediaPurpose'],
                    ownerType: $mediaPayload['ownerType'],
                    storedFileName: $$mediaPayload['storedFileName'],
                    scope: MediaStorageScope::PRIVATE,
                );
                $this->storage->remove(
                    params: $params,
                    mimeType: $mediaPayload['mime'],
                    recursive: true
                );
            }

            throw $exception;
        }

        return new AccountRegister(
            $candidateId->value(),
            $failedUploads
        );
    }
}