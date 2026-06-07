<?php

namespace App\Application\Usecases\JobOffer;


use App\Application\DTO\JobOffer\ChangeJobOffferRequest;
use App\Domain\JobOffer\JobOfferRepositioryInterface;


class JobOfferModifier
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    /**
     * This function is a usecase that allow any user to change information about a job stored in the bdd
     * @throws RessourceNotFound|\DomainException
     * @return void;
     */
    public function execute(
        string $userId,
        ChangeJobOffferRequest $command
    ):void
    {
        $offer = $this->repository->findById($userId, $command->uuid);

        if($command->title){
            $offer->rename($command->title);
        }

        if($command->content){
            $offer->changeContent($command->content);
        }

        $this->repository->change($offer, $command->uuid, $userId);
    }
}