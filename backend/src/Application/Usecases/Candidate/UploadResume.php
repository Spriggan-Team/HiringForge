<?php

namespace App\Application\Usecases\Candidate;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Candidate\CandidateResumeUploadResult;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Candidate\CandidateResume;
use App\Domain\Candidate\CandidateResumeRepositoryInterface;

use App\Domain\File\FileRepositoryInterface;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageInterface;

use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\TransactionManagerInterface;

class UploadResume
{
    public function __construct(
        private CandidateRepositoryInterface $candidateRepo,
        private CandidateResumeRepositoryInterface $candidateResumeRepo,
        private MediaFactoryInterface $mediaFactory,
        private MediaStorageInterface $mediaStorage,
        private TransactionManagerInterface $transactionManager,
        private FileRepositoryInterface $fileRepository
    ){}


    /**
     * Add resume to candidate
     */
    public function execute(string $candidateId, mixed $file): CandidateResumeUploadResult
    {
        if (!$this->candidateRepo->exists($candidateId)) {
            throw new \DomainException("Candidate was not found");
        }

        $media = $this->mediaFactory->createStaticMedia($file, expectedTypes: ["application/pdf"]);
        
        // Temp save
        $tempPath = $this->mediaStorage->storeTemp($file, storedFileName: $media->name);

        try {
            // Atomic BDD  Transaction  
            $resume = $this->transactionManager->execute(function () use ($media, $candidateId): CandidateResume {
                $media->id = $this->fileRepository->save($media);
                
                $resume = new CandidateResume(fileId: $media->id, candidateId: $candidateId);
                $this->candidateResumeRepo->save($resume);

                return $resume;
            });

            //-- Deplace Temp file to final destination
            $params = AccountStorageParams::resumes(
                candidateId: $candidateId,
                storedFileName: $media->name
            );
            $this->mediaStorage->moveToFinal($tempPath, $params);

            return new CandidateResumeUploadResult(resume: $resume, media: $media);
        }
        catch (\Throwable $e) {
            //-- Purge temp file
            $this->mediaStorage->deleteTemp($tempPath);
            throw $e;
        }
    }
}