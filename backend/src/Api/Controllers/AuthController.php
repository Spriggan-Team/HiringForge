<?php

namespace App\Api\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    
    #[Route('/user/login', name: 'app_user_login', methods: ['POST'])]
    #[Route('/agent/login', name: 'app_agent_login', methods: ['POST'])]
    #[Route('/candidate/login', name: 'app_candidate_login', methods: ['POST'])]
    public function login(): void
    {
        throw new \LogicException('This line will never be reached; it will be blocked by the firewall.');
    }
}