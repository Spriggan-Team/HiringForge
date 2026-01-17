<?php


namespace App\Application\Command\Usecase\JobOffer;


use App\Domain\User\UserId;
use App\Api\DTO\JobOffer\MutateJobOfferRequest;
use App\Domain\Repositories\JobOfferRepositioryInterface;



class JobOfferModifier
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(MutateJobOfferRequest $command):void
    {
        $offer = $this->repository->getById($command->accountId, $command->uuid);
        if($command->title){
            $offer->rename($command->title);
        }

        if($command->content){
            $offer->changeContent($command->content);
        }

        $this->repository->save($offer, UserId::fromString($command->uuid));
    }
}