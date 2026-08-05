<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/interviews')]
class InterviewsQueryManagement extends AbstractController{

    public function __construct(
        LoggerInterface $logger,
        private InterviewsRepositoryInterface $interviewsRepository
    )
    {
        ApiResponse::init($logger);
    }

    #[Route('/job_offer/{offerId}' ,methods: ['GET'])]
    public function getUserInterviews(
        string $offerId,
        Request $request
    ){
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $result = $this->interviewsRepository;

        }
        catch(\Exception $error){

        }
    }
}