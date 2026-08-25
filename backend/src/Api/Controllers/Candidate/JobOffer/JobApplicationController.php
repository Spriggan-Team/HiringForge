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

}