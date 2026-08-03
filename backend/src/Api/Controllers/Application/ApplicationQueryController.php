<?php

namespace App\Api\Controllers\Application;


use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('application')]
class ApplicationQueryController extends AbstractController
{
    #[Route()]
    public function getRejected(){

    }

}