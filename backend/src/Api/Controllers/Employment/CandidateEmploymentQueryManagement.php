<?php


namespace App\Api\Controllers\Employment;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Domain\EmploymentOffer\CandidateEmploymentOfferReaderInterface;
use App\Domain\EmploymentOffer\EmploymentOfferMenu;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route('/candidates/employment_offers')]
class CandidateEmploymentQueryManagement extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

    use ApiControllerHelpers;

    /**
     * Retreive employment offer for candidate
     * Queries:
     *  - skip?: number
     *  - limit?: number
     *  - jobTitle?: string
     *  - menu: "PENDING" | "ACCEPTED" | "COMPLETED" | "REJECTED" | "ALL"
     */
    #[Route('/', methods: ['GET'])]
    public function getEmploymentOfferView(
        Request $request,
        CandidateEmploymentOfferReaderInterface $repositorty,
        PathResolverInterface $pathResolver
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            if(!$candidateId){
                ApiResponse::$logger->error("CandidateId not found");
                return ApiResponse::error(
                    message:  "Something went wrong while retreiving employment offer for candidate"
                )->toJsonResponse();
            }


            //-- Queries param
            $skipParam = $request->query->get("skip", null);
            $limitParam = $request->query->get("limit", null);
            $menuParam = $request->query->get("menu", null);

            $jobTitle = $request->query->get("jobTitle", null);
            $menu = $menuParam ? EmploymentOfferMenu::from($menuParam) : EmploymentOfferMenu::ALL;

            $skip = is_numeric($skipParam) ? (int) $skipParam : 0;
            $limit = is_numeric($limitParam) ? (int) $limitParam : 15;

            $results = $repositorty->fetchEmploymentOffersForCandidate(
                candidateId: $candidateId,
                skip: $skip,
                limit: $limit,
                criteria: [
                    'jobTitle' => $jobTitle,
                    'menu' => $menu
                ],
            );

            $map = array_map(
                function ($item) use($request, $pathResolver) {
                    $company = $item['company'];
                    $logoUrl = null;

                    if ($company['logo'] !== null) {
                        $logoUrl = $this->resolvePublicImageUrl(
                            request: $request,
                            params: AccountStorageParams::companyLogo(
                                companyId: $company['id']
                            ),
                            fileName: $company['logo']['name'],
                            mime: $company['logo']['mime'],
                            pathResolver: $pathResolver
                        );
                    }

                    $item['company'] = [
                        'id' => $company['id'],
                        'name' => $company['name'],
                        'logoUrl' => $logoUrl,
                    ];

                    return $item;
                },
                $results['data']
            );

            return ApiResponse::success(
                data: [
                   'data' => $map,
                   'total' => $results['total']
                ],
                message: "Everything is okay"
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving employment offers for candidate",
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Retreive employment stats/kpi for candidates
     */
    #[Route('/stats')]
    public function getStats(
        CandidateEmploymentOfferReaderInterface $repository
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson */
            $candidate = $this->getUser();
            $candidateId = $candidate->getId();

            if(!$candidateId){
                ApiResponse::$logger->error("CandidateId not found");
                return ApiResponse::error(
                    message:  "Something went wrong while retreiving employment offer stats for candidate"
                )->toJsonResponse();
            }

            $stats = $repository->fetchEmploymentOffersStatsForCandidate($candidateId);
            
            return ApiResponse::success(
                data: $stats,
                message: "OK"
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            return ApiResponse::error(
                message: "Something went wrong while retreiving employment stats for candidate",
                throwable: $error
            )->toJsonResponse();
        }
    }
}