<?php


namespace App\Application\Command\Usecase\JobOffer;

use App\Domain\File\StaticMedia;
use App\Domain\User\UserId;
use App\Domain\JobOffer\JobOfferRepositioryInterface;

class JobOfferModifier
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    /**
     * This function is a usecase that allow any user to change information about a job stored in the bdd
     * @throws RessourceNotFound|DomainException
     * @return void;
     */
    public function execute(
        string $userId,
        string $jobOfferId,
        ?string $title,
        ?array  $content,
    ):void
    {
        $offer = $this->repository->findById($userId, $jobOfferId);

        if($title){
            $offer->rename($title);
        }

        if($content){
            $offer->changeContent($content);
        }

        $this->repository->change($offer, $jobOfferId, $userId);
    }
}