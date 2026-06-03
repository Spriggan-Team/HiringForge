<?php

namespace App\Api\Controllers\User\Agent\Assignement;

use App\Domain\Shared\Account\AccountRole;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(AccountRole::USER)]
#[Route("/users/agents/assignement")]
class AssignementQueryController extends AbstractController{
    
    #[Route("/")]
    public function getAll(){

    }

    #[Route("/{id}")]
    public function getById(string $id){

    }
}