<?php

namespace App\Application\Command\Usecase\JobOffer;

use Ramsey\Uuid\Uuid; 
use App\Domain\Entity\Post;
 
use App\Api\DTO\JobOffer\CreateJobOfferRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\JobOfferRepository;



class JobOfferRecorder
{
    public function __construct(private JobOfferRepository $repository){}

    public function execute(CreateJobOfferRequest $command): void
    {
        $today = new  \DateTimeImmutable();
        
        $offre = new Post(
            id: Uuid::uuid4(),
            title: $command->title,
            content: $command->content,
            createdAt: $today,
            updatedAt: $today
        );
        
        $this->repository->save($offre, $command->accountId);
    }
}

?>