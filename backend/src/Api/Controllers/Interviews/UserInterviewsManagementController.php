<?php


namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Api\Controllers\Interviews\Mapper\CreateInterviewMapper;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Interviews\InterviewCanceller;
use App\Application\Usecases\Interviews\InterviewGenerator;
use App\Application\Usecases\Interviews\InterviewsEraser;

use App\Domain\ApplicationErrorCode;
use App\Domain\Exception\ConcurrentInterviewsException;
use App\Domain\Exception\UnableResourceDeletion;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;



#[Route("/interviews/users")]
class UserInterviewsManagementController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    )
    {
        ApiResponse::init($logger);
    }

    /**
     * Create interviews
     */
    #[Route('/create', methods: ['POST'])]
    public function createInterview(
        Request $request,
        CreateInterviewMapper $mapper,
        InterviewGenerator $handler
    ){
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $command = $mapper->fromArray($body);
            
            $handler->execute(userId: $user->getId(),  command: $command);
            return ApiResponse::notice(
                message: "Interview created successfuly !"
            )->toJsonResponse();
        }
        catch(ConcurrentInterviewsException $error){
            return ApiResponse::error(
                message: "Found concurrently interviews error",
                statusCode: 400,
                throwable: $error,
                code: ApplicationErrorCode::CONCURRENT_INTERVIEWS_FOUNDED
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            ApiResponse::$logger->error("Something went wrong ", [$error]);
            return ApiResponse::error(
                message: "Something went wrong while fetching interviews",
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }


    
    #[Route('/{interviewId}/delete', methods: ['DELETE'])]
    public function deleteInterviews(
        string $interviewId,
        InterviewsEraser $handler
    )
    {
        try{
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            if(!$user){
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $handler->execute(
                userId: $user->getId(),
                interviewId: $interviewId
            );

            return ApiResponse::notice(
                message: "The interviews has been deleted successfuly"
            )->toJsonResponse();
        }
        catch(UnableResourceDeletion $error){
            return ApiResponse::error(
                message: "Interviews already confirmed by candidate",
                code: ApplicationErrorCode::UNABLE_RESOURCE_DELETION,
                throwable: $error
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while fetching interviews",
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Cancel an interview.
     */
    #[Route('/{interviewId}/cancel', methods: ['PATCH'])]
    public function cancelInterview(
        string $interviewId,
        InterviewCanceller $handler
    ) {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 403
                )->toJsonResponse();
            }

            $handler->execute(
                userId: $user->getId(),
                interviewId: $interviewId
            );

            return ApiResponse::notice(
                message: 'Interview cancelled successfully.'
            )->toJsonResponse();

        }
        catch (\Exception $error) {
            return ApiResponse::error(
                message: 'Something went wrong while cancelling the interview.',
                statusCode: 400,
                throwable: $error
            )->toJsonResponse();
        }
    }
}
