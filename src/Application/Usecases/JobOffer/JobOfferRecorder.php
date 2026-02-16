<?php

namespace App\Application\Command\Usecase\JobOffer;

use Ramsey\Uuid\Uuid; 
 
use App\Domain\User\UserId;
use App\Domain\File\StaticMedia;
use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositioryInterface;



class JobOfferRecorder
{
    public function __construct(
        private JobOfferRepositioryInterface $repository
    ){}

    public function execute(
        string $title,
        array $content,
        string $accountId,
        array $categories,
    ): void
    {
        $accountId =  UserId::create($accountId);    

        $offre = JobOffer::create(
            id: Uuid::uuid4()->toString(),
            title: $title,
            content: $content,
            categories: $categories,
        );

        $this->repository->save($offre, $accountId);
    }
}

?>