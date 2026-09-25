<?php

namespace App\Api\Controllers\Interviews;

use App\Api\Responder\ApiResponse;
use App\Domain\Interviews\InterviewStatus;
use App\Domain\Shared\PathResolverInterface;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\Interviews\Repositories\CandidateInterviewsQueryRepositoryInterface;

use Psr\Log\LoggerInterface;
use App\Api\Controllers\Helpers\ApiControllerHelpers;

use App\Domain\Shared\AccountStorageParams;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route("/interviews/candidates")]
class CandidateQueryManagement extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private CandidateInterviewsQueryRepositoryInterface $queryRepository,
        private PathResolverInterface $pathResolver
    ){
        ApiResponse::init($logger);
    }

    use ApiControllerHelpers;

    /**
     * Route: /interviews/candidates
     * 
     * Retreives all interviews planned at provided date
     * 
     * Queries:
     *  - date: ISO,
     *  - skip: number
     *  - limit: number,
     *  - statuses: InterviewStatus[]
     */
    #[Route('/agenda')]
    public function getInterviewsAgenda(
        Request $request
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            //-- Date
            $rawDate = $request->query->get('date');
            $date = $rawDate !== null 
                ? new \DateTimeImmutable($rawDate) 
                : new \DateTimeImmutable();

            // Paganition params
            $skip = $request->query->getInt('skip', 0);
            $limit = $request->query->getInt('limit', 10);

            //-- Statuses
            $statuses = json_decode($request->query->get("statuses")) ?? [];

            if(!empty($statuses) && is_array($statuses)){
                foreach($statuses as $key => $status){
                    if(!is_string($statuses))
                        throw new \DomainException("Statuses invalid");
                    $statuses[$key] = InterviewStatus::tryFrom($status);
                }
            }

            $results = $this->queryRepository->getInterviewAgenda(
                candidateId: $candidateId,
                skip: $skip,
                limit: $limit,
                date: $date,
                statuses: $statuses
            );

            $mapped = array_map(
                function($element)use($request){
                    $company = $element['company'];
                    if($company['logo']){
                        $logoUrl = $this->resolvePublicImageUrl(
                            request: $request,
                            params: AccountStorageParams::companyLogo(
                                companyId: $company['id'],
                            ),
                            pathResolver: $this->pathResolver,
                            fileName: $company['logo']['name'],
                            mime: $company['logo']['mime']
                        );

                        $element['company']['logoUrl'] = $logoUrl;

                        unset($element['company']['logo']);
                        unset($element['company']['id']);
                    }
                    return $element;
                },
                $results
            );

            return ApiResponse::success(
                data: $mapped,
                message: "Everything went smoothly when retreiving candidate agenda"
            )->toJsonResponse();
        }
        catch(\Exception $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving agenda",
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Retreive interiews for calendar display
     * Queries:
     *  - date: ISO,
     */
    #[Route('/calendar', methods: ["GET"])]
    public function getInterviewsCalendar(
        Request $request
    ){
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            $currentMonthParam = $request->query->get("currentMonth");

            if(!$currentMonthParam){
                return ApiResponse::error(
                    message: "\'currentMonth\' query is mandatory"
                )->toJsonResponse();
            }

            $currentMonth = new \DateTimeImmutable($currentMonthParam);

            $data = $this->queryRepository->getCalendarCollectionViews(
                candidateId: $candidateId,
                month: $currentMonth
            );

            
            return ApiResponse::success(
                data: $data,
                message: "Everything is okay"
            )->toJsonResponse();
        }
        catch(\Throwable $throwable){
            return ApiResponse::error(
                message: "Something went wrong while retreiving interview for candidate",
                statusCode: Response::HTTP_BAD_REQUEST,
                throwable: $throwable
            )->toJsonResponse();
        }
    }

}