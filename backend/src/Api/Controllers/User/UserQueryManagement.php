<?php

namespace App\Api\Controllers\User;

use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;

use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\User\UserRepositoryInterface;


use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



#[Route("/users")]
class UserQueryManagement extends AbstractController
{
    use ApiControllerHelpers;


    public function __construct(
        private LoggerInterface $logger,
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponse::init($logger);
    }


    
    #[Route("/kpi", methods: ['GET'], name: "view_kpi_metrics")]
    public function getKpi(
        JobOfferQueryRepositoryInterace $jobOfferQueryRepository
    ) {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $result = $jobOfferQueryRepository->analyseJobOfferCollection(userId: $user->getId());
            return ApiResponse::success(data: $result, message: "Everything went successfully")->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something wrong happened",
                throwable: $exception
            )->toJsonResponse();
        }
    }




    /**
     * Retrieve user information and related company information.
     */
    #[Route("", methods: ['GET'])]
    public function getCurrentUserContext(
        Request $request,
        UserRepositoryInterface $userQueryRepository,
        CompanyRepositoryInterface $companyRepository,
        MediaStorageInterface $mediaStorage,
        #[Autowire('%kernel.project_dir%')] string $projectDir
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $authenticatedUser */
            $authenticatedUser = $this->getUser();

            if (!$authenticatedUser) {
                return ApiResponse::error(
                    message: "User not authenticated",
                    statusCode: Response::HTTP_UNAUTHORIZED
                )->toJsonResponse();
            }

            // Retrieving the User Profile
            $user = $userQueryRepository->findById($authenticatedUser->getId());

            if (!$user) {
                return ApiResponse::error(
                    message: "User not found",
                    statusCode: Response::HTTP_NOT_FOUND
                )->toJsonResponse();
            }

            // Building the Avatar URL
            $avatar = null;
            if ($user->image()) {
                $avatar = $this->resolveUrl(
                    mediaStorage: $mediaStorage,
                    request: $request,
                    mimeType: $user->image()->mime,
                    fileName: $user->image()->name,
                    ownerId: $user->id(),
                    purpose: MediaPurpose::PROFILE,
                    ownerType: MediaOwnerType::USER,
                    projectDir: $projectDir
                );
            }

            $userData = [
                'id'        => $user->id(),
                'firstName' => $user->firstName(),
                'lastName'  => $user->lastName(),
                'email'     => $user->email(),
                'avatarUrl'    => $avatar,
            ];

            // Retrieval of company data, if applicable
            $companyData = null;
            if ($user->companyId()) {
                $company = $companyRepository->get($user->companyId());

                if ($company) {
                    //-- Formatting  adresses
                    $location = [];
                    foreach ($company->address() as $address) {
                        $location[] = [
                            "id"              => $address->id,
                            "city"            => $address->city,
                            "street"          => $address->street,
                            "country"         => $address->country,
                            "postalCode"      => $address->postalCode,
                            "visibilityRange" => $address->visibilityRange,
                        ];
                    }

                    //-- Logo URL Structure 
                    $logoURL = null;
                    if ($company->logo()) {
                        $logoURL = $this->resolveUrl(
                            mediaStorage: $mediaStorage,
                            request: $request,
                            mimeType: $company->logo()->mime,
                            fileName: $company->logo()->name,
                            ownerId: $company->id(),
                            purpose: MediaPurpose::PROFILE,
                            ownerType: MediaOwnerType::COMPANY,
                            projectDir: $projectDir
                        );
                    }

                    $companyData = [
                        'id'       => $company->id(),
                        'name'     => $company->name(),
                        'location' => $location,
                        'logoUrl'     => $logoURL,
                    ];
                }
            }

            //-- JSON Response
            return ApiResponse::success(
                data: [
                    'user'    => $userData,
                    'company' => $companyData,
                ],
                message: "Context retrieved successfully"
            )->toJsonResponse();
        }
        catch (\Exception $exception) {
            return ApiResponse::error(
                message: "Something wrong happened",
                throwable: $exception
            )->toJsonResponse();
        }
    }


}