<?php

namespace App\Api\Controllers\Agent;

use App\Domain\Shared\Account\AccountRole;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted(AccountRole::AGENT)]
class AgentJobOfferManagementController extends AbstractController{

}