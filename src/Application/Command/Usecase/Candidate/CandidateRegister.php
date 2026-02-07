<?php

namespace App\Application\Command\Usecase\Candidate;

use App\Api\DTO\Candidate\RegisterCandidateCommand;

use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateId;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\UnbaleToStoreFile;
use App\Domain\File\FileOwnerType;
use App\Domain\File\FilePurpose;
use App\Domain\File\FileStorageInterface;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Actor\ActorRegister;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;

class CandidateRegister
{
    public function __construct(
        private CandidateRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private FileStorageInterface $storage,
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
        ?StaticMedia $image = null,
        StaticMedia $cv,
    ): ActorRegister
    {
        $email = new EmailAddress($email);
        $identity = $this->repository->exists(null, $email);
        if($identity){
          throw new EmailAlreadyRegistered();
        }
        
        $candidateId = new  CandidateId();

        if($image){
            $uploadImageResult = $this->storage->store($image);
        }
        $uploadCVResult = $this->storage->store(
            file: $cv,
            userId: $candidateId->value(),
            ownerType: FileOwnerType::CANDIDATE,
            purpose: FilePurpose::CV
        );

        if(!(count($uploadCVResult->stored) > 0)){
            throw new UnbaleToStoreFile(); //An user must upload an image to be elligble t o this service
        }

        $candidate = Candidate::create(
            id: $candidateId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            passwordHash: $this->hasher->hash((new PlainPassword($password))->value()),
            image: $uploadImageResult->stored[0] ?? null,
            cv: $uploadCVResult->stored[0]
        );

        $this->repository->save($candidate);
        return new ActorRegister($candidateId->value(), $uploadImageResult->failed);
    }
}