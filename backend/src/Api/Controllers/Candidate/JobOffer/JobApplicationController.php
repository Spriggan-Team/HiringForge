<?php

namespace App\Api\Controllers\Candidate\JobOffer;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Candidate\ApplyToJobOffer;
use App\Domain\Shared\Account\AccountRole;


use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;




#[IsGranted(AccountRole::USER->value)]
#[Route('/candidate/job/{offerId}/application')]
class JobApplicationController extends AbstractController
{

    public function __construct(
        LoggerInterface $logger
    )
    {
        ApiResponse::init($logger);
    }

    /**
     * Here, we make an application for candidate to a particular job
     */
    #[Route('/', methods: ['POST'], name: 'candidate_application')]
    function apply(
        Request $request,
        string $offerId,
        ApplyToJobOffer $usecase
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson **/
            $candidate = $this->getUser();
            // $usecase->execute(candidateId: $candidate->getId(), offerId: $offerId);
            return ApiResponse::notice("Everything went smoothly")->toJsonResponse();
        }
        catch(Exception $exception)
        {
            return ApiResponse::error(message: "Something went wrong while applying", throwable: $exception)->toJsonResponse();
        }
    }

    
    public function uploadCV()
    {

    }
    
    /**
     * Here we undone an application made by a candidate depending on bisuness conditions
     */
    #[Route('/undone')]
    public function retire(){}
}