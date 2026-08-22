<?php

namespace App\Api\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    
    #[Route('/login', name: 'app_account_login', methods: ['POST'])]
    public function login(): void
    {
        throw new \LogicException('This line will never be reached; it will be blocked by the firewall.');
    }
}