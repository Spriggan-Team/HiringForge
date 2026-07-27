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


use App\Infrastructure\Storage\FileStorage\MediaFactory;



class CandidateRegisterUsecase
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CandidateRepositoryInterface $candidateRepository,
        private PasswordHasherInterface $hasher,
        private MediaStorageInterface $storage,
        private MediaFactory   $mediaFactory,
    ){}

    /**
     * @throws EmailAlreadyRegistered|RessourceNotFound
     * @return AccountRegister
     */
    public function execute(
        RegisterCandidateCommand $command
    ): AccountRegister
    {
        $email =  EmailAddress::create($command->email);
        
        $exist = $this->accountRepository->exists(null, $email->value());
        if($exist){
          throw new EmailAlreadyRegistered();
        }
        
        $candidateId = CandidateId::create();

        $failedUploads = [];

        $candidate = Candidate::create(
            id: $candidateId->value(),
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $email,
            passwordHash: $this->hasher->hash((new PlainPassword($command->password))->value()),
            address: $command->address,
            searchRadius: $command->searchRadius
        );

        if($command->image){
            $staticImage = $this->mediaFactory->createStaticMedia($command->image);
            $candidate->addImage($staticImage);

            $this->storage->store(
                $command->image,
                storedFileName: $staticImage->name,
                ownerId: $candidateId->value(),
                ownerType: MediaOwnerType::CANDIDATE,
                mediaPurpose: MediaPurpose::PROFILE,
                errorCallback: function($result) use (&$candidate, $staticImage, &$failedUploads,){
                    $failedUploads = $result->originalName;
                    $candidate->removeImage();
                }

            );
        }

        if($command->cv){
            $staticCV = $this->mediaFactory->createStaticMedia($command->cv);
            $candidate->setCv($staticCV);
              
            $this->storage->store(
                file: $command->cv,
                ownerId: $candidateId->value(),
                storedFileName: $staticCV->name,
                ownerType: MediaOwnerType::CANDIDATE,
                mediaPurpose: MediaPurpose::CV,
                errorCallback: function($result) use (&$candidate, &$failedUploads){
                    $failedUploads = $result->originalName;
                    $candidate->removeCv();
                }
            );
        }


        $this->candidateRepository->save($candidate);

        return new AccountRegister(
            $candidateId->value(),
            $failedUploads
        );
    }
}