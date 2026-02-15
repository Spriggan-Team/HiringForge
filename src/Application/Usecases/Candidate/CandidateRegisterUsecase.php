<?php

namespace App\Application\Usecases\Candidate;

use App\Application\Usecases\account\AccountRegister;
use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateId;

use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Candidate\CandidateRepositoryInterface;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;
use App\Infrastructure\Storage\FileStorage\MediaFactory;

class CandidateRegisterUsecase
{
    public function __construct(
        private CandidateRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private MediaStorageInterface $storage,
        private MediaFactory   $mediaFactory,
    ){}

    /**
     * @throws EmailAlreadyRegistered|RessourceNotFound
     * @return ActorRegister
     */
    public function execute(
        string $lastName,
        string $firstName,
        string $email,
        string $password,
        /** @var StaticMedia */
        mixed $uploadedImage = null,
        /** @var StaticMedia */
        mixed $uploadedCV,
        ?Address $address = null,
        ?int $searchRadius = null,
    ): AccountRegister
    {
        $email =  EmailAddress::create($email);
        $identity = $this->repository->exists(null, $email);
        if($identity){
          throw new EmailAlreadyRegistered();
        }
        
        $candidateId = CandidateId::create();

        $failedUploads = [];

        $candidate = Candidate::create(
            id: $candidateId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            passwordHash: $this->hasher->hash((new PlainPassword($password))->value()),
            address: $address,
            searchRadius: $searchRadius
        );

        if($uploadedImage){
            $staticImage = $this->mediaFactory->createStaticMedia($uploadedImage);
            $candidate->setImage($staticImage);

            $this->storage->store(
                $uploadedImage,
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

        if($uploadedCV){
            $staticCV = $this->mediaFactory->createStaticMedia($uploadedCV);
            $candidate->setCv($staticCV);
              
            $this->storage->store(
                file: $uploadedCV,
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


        $this->repository->save($candidate);

        return new AccountRegister(
            $candidateId->value(),
            $failedUploads
        );
    }
}