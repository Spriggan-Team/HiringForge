<?php

namespace App\Api\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class CandidateController extends AbstractController
{
    /**
     * This controller allow any connected user to access to its informations
     */
    #[Route('/candidate/{candidateId}')]
    public function getCandidateInformation(
        string $candidateId
    )
    {

    }

    public function changeProfilInformation()
    {

    }

    public function resetPassword()
    {

    }

    public function changeEmail()
    {}

    public function resetPasswordVerificationCode()
    {

    }

    public function uploadCV()
    {

    }


}