<?php

use App\Application\Command\Handlers\Category\CreateCategoryHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CategoryController extends AbstractController
{
    public function __construct(private LoggerInterface $logger){}

    #[Route('/categories', methods: ['GET'], name: 'get_all_categories')]
    public function getCategories(
        Request $request,
        CreateCategoryHandler $handler
    )
    {
        try{
            
        }
        catch(Exception $exception){
            $this->logger->error("Somethinf went wrong");
            return $this->json("Something went wrong");
        }
    }
}