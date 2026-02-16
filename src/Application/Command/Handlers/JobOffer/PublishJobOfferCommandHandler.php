<?php


namespace App\Application\Command\Handlers\JobOffer;

use App\Api\Responder\ApiResponse;
use App\Application\Command\Usecase\JobOffer\JobOffferPublisher;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class PublishJobOfferCommandHandler
{
    public function __construct(
        private JobOffferPublisher $publisher
    ){}

    public function handle(string $accountId, string $offerId): ApiResponse
    {
        try{
            if(!(Uuid::isValid($accountId)  && Uuid::isValid($offerId)))
            {
                throw new BadRequestHttpException("Bad value type");
            }
            $this->publisher->execute($accountId, $offerId);
            return ApiResponse::notice("Everything went smoothly");
        }
        catch(BadRequestHttpException $badRequest)
        {
            return ApiResponse::error(
                message: "Please check data format and try again!!", 
                throwable: $badRequest
            );
        }
    }
}