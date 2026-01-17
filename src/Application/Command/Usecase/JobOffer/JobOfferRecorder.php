<?php

namespace App\Application\Command\Usecase\JobOffer;

use Ramsey\Uuid\Uuid; 
 
use App\Api\DTO\JobOffer\CreateJobOfferRequest;

use App\Domain\JobOffer\JobOffer;
use App\Domain\User\UserId;
use App\Domain\Repositories\JobOfferRepositioryInterface;



class JobOfferRecorder
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(CreateJobOfferRequest $command): void
    {
        $offre = JobOffer::create(id: Uuid::uuid4(), title: $command->title, content: $command->content);
        $this->repository->save($offre, UserId::fromString($command->userId));
    }
}

?>