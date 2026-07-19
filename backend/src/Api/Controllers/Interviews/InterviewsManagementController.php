<?php


namespace App\Api\Controllers\Interviews;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;


#[Route("/interviews")]
class InterviewsManagementController extends AbstractController
{
    #[Route('/')]
    public function getInterviews(){

    }
}
