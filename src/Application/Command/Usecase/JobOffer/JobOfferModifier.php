<?php


namespace App\Application\Command\Usecase\JobOffer;


use App\Domain\ValueObject\MergeRule;
use App\Api\DTO\JobOffer\MutateJobOfferRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\JobOfferRepository;



class JobOfferModifier
{
    public function __construct(private JobOfferRepository $repository){}

    public function execute(MutateJobOfferRequest $command):void
    {
        $offer = $this->repository->getById($command->accountId, $command->uuid);
        if($command->title){
            $offer->setTitle($command->title);
        }

        if($command->content){
            $offer->setContent($command->content);
        }

        $this->repository->save($offer, $command->uuid, MergeRule::PARTIAL_MERGE);
    }
}