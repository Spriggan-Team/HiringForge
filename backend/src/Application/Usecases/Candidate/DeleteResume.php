<?php

namespace App\Application\Usecases\Candidate;

use App\Domain\Candidate\CandidateResumeDeletionValidator;
use App\Domain\Candidate\CandidateResumeRepositoryInterface;

use App\Domain\File\MediaStorageInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\TransactionManagerInterface;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DeleteResume
{
    public function __construct(
        private CandidateResumeRepositoryInterface $candidateResumeRepo,
        private TransactionManagerInterface $transactionManager,
        private MediaStorageInterface $mediaStorage,
        private CandidateResumeDeletionValidator $candidateResumeDeletionValidator
    ) {}


    public function execute(string $candidateId, string $fileId): void
    {
        // One-time data recovery
        $resumeMedia = $this->candidateResumeRepo->findResumeMediaOrNull(candidateId: $candidateId, fileId: $fileId);

        if (!$resumeMedia) {
            throw new ResourceNotFoundException("Resume not found for this candidate");
        }

        // Business Validation (Throws a specific DomainException if invalid)
        $this->candidateResumeDeletionValidator->assertCanBeDeleted(
            candidateId: $candidateId, 
            resumeId: $fileId
        );

        $mediaParams = AccountStorageParams::resumes(
            candidateId: $candidateId,
            storedFileName: $resumeMedia->name
        );


        //  Callbacks & Deletion
        $onStorageSuccess = function () use ($candidateId, $fileId): void {
            $this->transactionManager->execute(function () use ($candidateId, $fileId): void {
                $this->candidateResumeRepo->delete(candidateId: $candidateId, fileId: $fileId);
            });
        };

        $onStorageError = function (\Throwable $e): void {
            throw new \DomainException("Failed to delete physical file, database operation aborted.", 0, $e);
        };


        $this->mediaStorage->remove(
            params: $mediaParams,
            mimeType: $resumeMedia->mime,
            successCallback: $onStorageSuccess,
            errorCallback: $onStorageError
        );
    }
}
