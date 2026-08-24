<?php 

namespace App\Application\DTO\Candidate;

use App\Domain\File\Media;
use App\Domain\Candidate\CandidateResume;


readonly class CandidateResumeUploadResult
{
    public function __construct(
        public CandidateResume $resume,
        public Media $media
    ) {}
}