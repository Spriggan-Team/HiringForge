<?php

namespace App\Api\Controllers\Candidate;

use App\Domain\Shared\Account\AccountRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This one is used for candidate management;
 * It requires CANDIDATE permissions
 */
#[IsGranted(AccountRole::CANDIDATE->value)]
class CandidateQueryManagementController extends AbstractController
{
    /**
     * This controller allow any connected user to access to its informations
     */
    #[Route('/candidate/profile/{candidateId}', methods: ['GET'])]
    public function getCandidateInformation()
    {

    }

    public function changeProfilInformation()
    {

    }




}