<?php


namespace App\Api\Controllers\Employment;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Candidate\AcceptEmploymentOffer;
use App\Application\Usecases\Candidate\RefuseEmployementOffer;
use Psr\Log\LoggerInterface;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/candidates')]
class CandidateEmploymentManagement extends AbstractController
{

    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

    
    /**
     * Accept An employment offers
     * - Body : {
     *      employmentId: string
     *  }
     */
    #[Route('/employment_offers/accept', methods: ['PATCH'])]
    public function accept(
        Request $request,
        AcceptEmploymentOffer $handler
    )
    {
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $handler->execute(
                candidateId: $candidate->getId(),
                employmentOfferId: $body['employmentId']
            );

            return ApiResponse::notice(
                message: "Everything went successfuly while accepting employement offer"
            )->toJsonResponse();
        }
        catch(\Throwable $throwable){
            return ApiResponse::error(
                message: "Something went wrong while accepting employment offer",
                statusCode: Response::HTTP_BAD_REQUEST,
                throwable: $throwable
            )->toJsonResponse();
        }
    }



    /**
     * Refuse An employment offers
     * - Body : {
     *      employmentId: string
     *      rejectionReason: string
     *  }
     */
    #[Route('/employment_offers/refuse', methods: ['PATCH'])]
    public function refuse(
        Request $request,
        RefuseEmployementOffer $handler
    ){
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $handler->execute(
                candidateId: $candidate->getId(),
                employmentOfferId: $body['employmentId'],
                rejectionReason: $body["rejectionReason"]
            );

            return ApiResponse::notice(
                message: "Everything went successfuly while refusing employement offer"
            )->toJsonResponse();
        }
        catch(\Throwable $throwable){
            return ApiResponse::error(
                message: "Something went wrong while refusing employment offer",
                statusCode: Response::HTTP_BAD_REQUEST,
                throwable: $throwable
            )->toJsonResponse();
        }
    }
}