<?php


namespace App\Api\Controllers\Employment;

use App\Api\Responder\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CandidateEmploymentQueryManagement extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }
}