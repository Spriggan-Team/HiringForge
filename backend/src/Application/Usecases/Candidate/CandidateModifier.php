<?php

namespace App\Application\Usecases\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Candidate\ChangeCandidateCommand;
use App\Domain\Candidate\Candidate;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\EmailAddress;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Candidate\CandidateSkillRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\File\MediaStorageInterface;




final class CandidateModifier
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepository,
        private CandidateSkillRepositoryInterface $candidateSkillRepository,
        private AccountRepositoryInterface $accountRepositoryInterface,
        private MediaStorageInterface $mediaStorage,
        private MediaFactoryInterface $mediaFactory,
    ) {}

    public function execute(ChangeCandidateCommand $command): void
    {
        $candidate = $this->candidateRepository->findById($command->id);

        if ($candidate === null) {
            throw new \DomainException('Candidate not found.');
        }

        // -----------------------------------------
        // Basic information
        // -----------------------------------------

        $candidate->rename(
            firstName: $command->firstName,
            lastName: $command->lastName,
        );


        $candidate->changeEmail(
            email: EmailAddress::hydrate($command->email),
        );

        $candidate->setDescription($command->description);
        $candidate->setAddress($command->address);

        // -----------------------------------------
        // Skills
        // -----------------------------------------

        $diff = $candidate->skillsDiff($command->skills);

        foreach ($diff->removed as $skill) {
            $this->candidateSkillRepository->unlink(
                candidateId: $candidate->id(),
                skillId: $skill->id(),
            );
        }
   

        foreach ($diff->added as $skill) {
            $this->candidateSkillRepository->link(
                candidateId: $candidate->id(),
                skillId: $skill->id(),
            );
        }

        // -----------------------------------------
        // Profile image
        // -----------------------------------------

        if ($command->image !== null) {
            $storageParams = AccountStorageParams::candidateProfileImage(
                candidateId: $candidate->id(),
            );

            $oldMedia = $this->accountRepositoryInterface->getProfileImage(
                $candidate->id(),
            );

            $newMedia = $this->mediaFactory->createStaticMedia(
                $command->image,
                expectedTypes: [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
            );


            if($newMedia->name != $oldMedia->originalName){
                // Store new image
                $this->mediaStorage->store(
                    file: $command->image,
                    params: $storageParams,
                );
    
                try {
                    // Update bdd reference
                    $this->accountRepositoryInterface->changeProfileImage(
                        accountId: $candidate->id(),
                        image: $newMedia,
                    );
                }
                catch (\Throwable $error) {
                    // Delete new file
                    $this->mediaStorage->remove(
                        params: $storageParams,
                        fileName: $newMedia->name,
                    );
    
                    throw $error;
                }
    
                // Delete old image after bdd success
                if ($oldMedia !== null) {
                    $this->mediaStorage->remove(
                        params: $storageParams,
                        fileName: $oldMedia->name,
                    );
                }
            }
        }

        $this->candidateRepository->save($candidate);
    }
}