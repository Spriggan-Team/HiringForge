<?php

namespace App\Api\Controllers\User\Agent;

use App\Domain\Shared\Account\AccountRole;

use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/users/agent")]
#[IsGranted(AccountRole::USER)]
class AgentAccountManagementController extends AbstractController{
    #[Route("/", methods: "")]
    public function create(
        Request $request
    ){

    }
}