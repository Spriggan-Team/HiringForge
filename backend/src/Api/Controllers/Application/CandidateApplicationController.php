<?php


namespace App\Api\Controllers\Application;

use App\Api\Controllers\Helpers\ApiControllerHelpers;
use Psr\Log\LoggerInterface;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Candidate\ApplyToJobOffer;
use App\Domain\Candidate\Application\Repositories\ApplicationMenu;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRole;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handle user applications services
 */
#[Route('/applications/candidate')]
#[IsGranted(AccountRole::CANDIDATE->value)]
class CandidateApplicationController extends AbstractController
{

    use ApiControllerHelpers;

    public function __construct(
        private LoggerInterface $logger,
        private CandidateRepositoryInterface $candidateRepository,
        private ApplicationRepositoryInterface $applicationRepository,
    ){
        ApiResponse::init($logger);
    }
    

    /**
     * Handle application candidate stats
     */
    #[Route('/stats', methods: ['GET'])]
    public function getApplicationStats(){
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();

            $results = $this->applicationRepository->getCandidateApplicationStats(candidateId: $candidate->getId());
            
            return ApiResponse::success(
                data: $results,
                message: "Applications stats retrieved with success"
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            return ApiResponse::error(
                message: "Something wznt wrong while retreiviong applications stats",
                throwable: $error
            )->toJsonResponse();
        }
    }


    /**
     * Retrieve the application collection for the authenticated candidate.
     *
     * Query parameters:
     * - skip?: int
     * - limit?: int
     * - menu?: string
     */
    #[Route('/my-applications', methods: ['GET'])]
    public function getMyApplications(
        Request $request,
        PathResolverInterface $pathResolver
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();

            $skip = max( 0, $request->query->getInt('skip', 0));
            $limit = max(1,$request->query->getInt('limit', 15));

            $menuValue = $request->query->get(
                'menu',
                ApplicationMenu::ALL->value
            );

            $sectionType = ApplicationMenu::tryFrom($menuValue) ?? ApplicationMenu::ALL;

            $results = $this->applicationRepository->getApplicationsViewCollection(
                candidateId: $candidate->getId(),
                skip: $skip,
                limit: $limit,
                sectionType: $sectionType
            );

            /*
            * Map stored image files to public URLs.
            */
            $data = array_map(
                function (array $item) use ($request, $pathResolver): array {
                    $jobImageName = $item['job']['image']['name'];
                    $jobImageMime = $item['job']['image']['mime'];

                    $companyLogoName = $item['company']['logo']['name'];
                    $companyLogoMime = $item['company']['logo']['mime'];

                    /*
                    * Resolve job image.
                    */
                    $item['job']['image'] = $this->resolvePublicImageUrl(
                        request: $request,
                        pathResolver: $pathResolver,
                        params: AccountStorageParams::companyJobImages(
                            companyId: $item['company']['id'],
                            storedFileName: $jobImageName
                        ),
                        mime: $jobImageMime
                    );

                    /*
                    * Resolve company logo.
                    */
                    $item['company']['logo'] = $this->resolvePublicImageUrl(
                        request: $request,
                        pathResolver: $pathResolver,
                        params: AccountStorageParams::companyLogo(
                            companyId: $item['company']['id'],
                            storedFileName: $companyLogoName
                        ),
                        mime: $companyLogoMime
                    );

                    return $item;
                },
                $results['data']
            );

            return ApiResponse::success(
                message: 'Everything is okay',
                data: [
                    'data' => $data,
                    'total' => $results['total'],
                ]
            )->toJsonResponse();
        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while retrieving candidate applications',
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Retreive details about job
     * Queries:
     *  - locale?: string -> language code
     */
    #[Route('/{applicationId}', methods: ['GET'])]
    public function getApplicationDetails(
        Request $request,
        string $applicationId
    ): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson $candidate */
            $candidate = $this->getUser();
            $locale = $request->query->get("locale", null);

            $results = $this->applicationRepository->getApplicationDetails(
                candidateId: $candidate->getId(), 
                applicationId: $applicationId,
                code: $locale ?? "fr",
            );

            return ApiResponse::success(
                data: $results,
                message: "Applications details retreived successfully for candidate"
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }
    
    

    /**
     * Retreive job ids of 
     * all related applications this user
     */
    #[Route('/jobs/ids', methods: ['GET'])]
    public function getApplicationsIds(){
        try{
            /** @var AuthenticatedPerson|null  $candidate*/
            $candidate = $this->getUser();
            
            if(!$candidate){
                return ApiResponse::error(
                    message: "Unauthorize action",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            $candidateId = $candidate->getId();
            $ids = $this->candidateRepository->getCandidateApplicationJobIds(candidateId: $candidateId);

            return ApiResponse::success(
                data: $ids,
                message: "Everything is okay"
            )->toJsonResponse();
        }
        catch(\Throwable $error){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $error
            )->toJsonResponse();
        }
    }



    /**
     * Handle candidate application action
     */
    #[Route('/{jobId}/apply')]
    public function apply(
        string $jobId,
        Request $request,
        ApplyToJobOffer $usecase
    ){
        try{
            /** @var AuthenticatedPerson|null  $user*/
            $user = $this->getUser();

            if(!$user){
                return ApiResponse::error(
                    message: "Unauthorize actions",
                    statusCode: 401
                );
            }
            $body = json_decode($request->getContent(), true);
            $fileId = $body["fileId"];

            if(!$fileId){
                return ApiResponse::error(
                    message: "fileId must be provided",
                    statusCode: 400
                )->toJsonResponse();
            }

            $applicationId = $usecase->execute(
                candidateId: $user->getId(),
                offerId: $jobId,
                fileId: $fileId
            );

            return ApiResponse::success(
                data: $applicationId,
                message: "Everything went right"
            )->toJsonResponse();
        }   
        catch(\Throwable $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception
            )->toJsonResponse();
        } 
    }
}