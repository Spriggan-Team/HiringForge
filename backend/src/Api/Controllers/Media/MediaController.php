<?php

namespace App\Api\Controllers\Media;

use App\Api\Responder\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;



class MediaController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

}