<?php

namespace App\Application\Command\Usecase\JobOffer;

use Ramsey\Uuid\Uuid; 
 
use App\Api\DTO\JobOffer\CreateJobOfferRequest;

use App\Domain\JobOffer\JobOffer;
use App\Domain\User\UserId;
use App\Domain\Repositories\JobOfferRepositioryInterface;



class JobOfferRecorder
{
    public function __construct(
        private JobOfferRepositioryInterface $repository
    ){}

    public function execute(
        string $userId,
        string $title,
        array $content,
        array $categories
    ): void
    {
        $userId = new UserId($userId);    

        $offre = JobOffer::create(
            id: Uuid::uuid4()->toString(),
            title: $title,
            content: $content,
            categories: $categories
        );

        $this->repository->save($offre, $userId);
    }
}

?>