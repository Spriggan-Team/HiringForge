<?php


namespace App\Application\Usecases\Interviews;

use App\Domain\Exception\UnableResourceDeletion;
use App\Domain\Interviews\InterviewsRepositoryInterface;

class InterviewsEraser
{
    public function __construct(
        private InterviewsRepositoryInterface $interviewRepository
    )
    {}

    public function execute(string $userId, string $interviewId){
        //-- Check requirement
        $association = $this->interviewRepository->isUserAssociatedWithInterview(userId: $userId, interviewId:$interviewId);

        if(!$association){
            throw new \DomainException("This user cannot access to the provided interview");
        }

        if($this->interviewRepository->isConfirmedByCandidate(interviewId: $interviewId)){
            throw new UnableResourceDeletion();
        }

        $this->interviewRepository->remove($interviewId);
    }
}