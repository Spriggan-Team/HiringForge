<?php

namespace App\Api\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/test')]
class TestController extends AbstractController 
{
    #[Route('/', methods: ['POST'])]
    public function test(){
        return new JsonResponse(["result"=> "Ok"]);
    }
}


?>