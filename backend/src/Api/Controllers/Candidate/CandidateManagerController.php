<?php


namespace App\Api\Controllers\Candidate;

use App\Api\Controllers\Candidate\Mapper\ChangeCandidateMapper;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Candidate\CandidateModifier;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route("/candidates")]
class CandidateManagerController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }


    #[Route('/profile/details', methods: ['POST'])]
    public function changeCandidateProfil(
        Request $request,
        CandidateModifier $handler,
    ): Response {
        try {
            /** @var AuthenticatedPerson|null $candidate */
            $candidate = $this->getUser();

            if (!$candidate) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 401,
                )->toJsonResponse();
            }


            $candidateId = $candidate->getId();

            $body = $request->request->all();

            // Fields multipart/form-data
            $location = json_decode(
                $body['location'] ?? '{}',
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            // Skills : JSON contained in a multipart field
            $skills = json_decode(
                $body['skills'] ?? '[]',
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            // File
            $command = ChangeCandidateMapper::fromArray(
                id: $candidateId,
                context: $body,
                skills: $skills,
                location: $location,
                image: $request->files->get('image'),
            );

            $handler->execute($command);

            return ApiResponse::notice(
                message: 'Candidate profile updated successfully.',
            )->toJsonResponse();

        }
        catch (\Throwable $error) {
            $this->logger->error(
                'Failed to update candidate profile.',
                [
                    'exception' => $error,
                ],
            );

            return ApiResponse::error(
                message: "Something went wrong while updating profile",
                throwable: $error
            )->toJsonResponse();
        }
    }


}