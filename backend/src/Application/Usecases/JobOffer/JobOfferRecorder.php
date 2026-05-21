<?php

namespace App\Application\Command\Usecase\JobOffer;

use App\Application\DTO\JobOffer\CreateJobOffer;
use App\Domain\Category\Repositories\CategoryRepositoryInterace;
use Ramsey\Uuid\Uuid; 
 
use App\Domain\User\UserId;
use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositioryInterface;



class JobOfferRecorder
{
    public function __construct(
        private JobOfferRepositioryInterface $repository,
        private CategoryRepositoryInterace $categoryRepository,
    ){}

    public function execute(
        string $accountId,
        CreateJobOffer $command
    ): string
    {
        $accountId =  UserId::create($accountId);    
        $this->categoryRepository->asserCategoriesExistence($command->categories);

        $offre = JobOffer::create(
            id: Uuid::uuid4()->toString(),
            title: $command->title,
            content: $command->content,
            categories: $command->categories,
        );

        $this->repository->save($offre, $accountId);
        return $offre->id();
    }
}

?>