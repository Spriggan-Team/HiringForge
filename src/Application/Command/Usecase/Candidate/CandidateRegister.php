<?php

namespace App\Application\Command\Usecase\Candidate;

use App\Api\DTO\Candidate\RegisterCandidateCommand;

use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateId;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\UnbaleToStoreFile;

use App\Domain\File\FileStorageInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;


class CandidateRegister
{
    public function __construct(
        private CandidateRepositoryInterface $repository,
        private PasswordHasherInterface $hasher,
        private FileStorageInterface $storage,
    ){}

    /**
     * @return array{0: string, 1: string[]} A tuple where the first element is the CandidateId object,
     *                                          and the second element is an array of filenames that failed to upload
     */
    public function execute(RegisterCandidateCommand $command): array
    {

        $candidate = $this->repository->findByEmail($command->email);
        if($candidate){
          throw new EmailAlreadyRegistered();
        }
        
        $candidateId = new  CandidateId();
        
        $uploadImageResult = $this->storage->store([$command->image]);
        $uploadCVResult = $this->storage->store([$command->cv]);

        if(count($uploadCVResult->failed) > 0){
            throw new UnbaleToStoreFile(); //An user must upload an image to be elligble t o this service
        }

        $candidate = Candidate::create(
            id: $candidateId,
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: new EmailAddress($command->email),
            passwordHash: $this->hasher->hash($command->password),
            image: $uploadImageResult->stored[0] ?? null,
            cv: $uploadCVResult->stored[0]
        );

        $this->repository->save($candidate);
        return [$candidateId->value(), $uploadImageResult->failed];
    }
}