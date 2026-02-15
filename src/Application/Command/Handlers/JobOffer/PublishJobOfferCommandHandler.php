<?php


namespace App\Application\Command\Handlers\JobOffer;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\JobOffer\JobOffferPublisher;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublishJobOfferCommandHandler
{
    public function __construct(
        private JobOffferPublisher $publisher
    ){}

    public function handle(string $userId, string $offerId)
    {
        try{
            if(!(Uuid::isValid($userId)  && Uuid::isValid($offerId)))
            {
                throw new BadRequestHttpException("Bad value type");
            }
            $this->publisher->execute($userId, $offerId);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(BadRequestHttpException $badRequest)
        {
            return ApiResponseBuilder::error("Please check data format and try again!!", $badRequest);
        }
    }
}