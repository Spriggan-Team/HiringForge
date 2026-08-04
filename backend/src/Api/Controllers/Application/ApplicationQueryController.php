<?php

namespace App\Api\Controllers\Application;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/application')]
class ApplicationQueryController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private ApplicationRepositoryInterface $applicationRepository
    )
    {
        ApiResponse::init($logger);
    }
    

    #[Route('/{jobOfferId}/rejected')]
    public function getRejected(
        string $jobOfferId
    ){
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: 'Something went wrong',
                statusCode: 400
            )->toJsonResponse();
        }
    }

}