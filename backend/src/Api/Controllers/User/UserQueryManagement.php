<?php

namespace App\Api\Controllers\User;

use App\Api\Controllers\Helpers\ApiControllerHelpers;
use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Query\JobOffer\Repositories\JobOfferAnalyticsRepositoryInterface;

use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Company\CompanyViewRepositoryInterface;

use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\PathResolverInterface;
use App\Domain\User\UserRepositoryInterface;


use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;


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
        private PathResolverInterface $pathResolver
    ) {
        //This is mandatory that permit ApiResponseBuilder to log exception in a special format
        //It purpose is to reduce the resposability of the http controller.
        ApiResponse::init($logger);
    }


    /**
     * Retrieve profile details for the authenticated recruiter.
     *
     * Query:
     *  - companyId: string
     */
    #[Route('/profile/view', methods: ['GET'])]
    public function getProfileView(
        Request $request,
        CompanyViewRepositoryInterface $companyRepository,
        UserRepositoryInterface $userRepository
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $companyId = $request->query->get('companyId');

            $companyData = $companyRepository->getCompanyViewById($companyId);
            $userData = $userRepository->getRecruiterView($user->getId());

            //-- Build public image uri
                //-- Company

            if($companyData['logo']){
                $companyData['logo'] = [
                    "id" => $companyData['logo']["id"],
                    "url" => $this->resolvePublicImageUrl(
                        request: $request,
                        params: AccountStorageParams::companyLogo(
                            companyId: $companyId
                        ),
                        pathResolver: $this->pathResolver,
                        fileName: $companyData['images']['main']['name'],
                        mime: $companyData['images']['main']['mime']
                    )
                ];
            }
            
            $companyImageParams = AccountStorageParams::companyImages(
                companyId: $companyId
            );

                //-- main image
            if ($companyData['images']['main'] !== null) {
                $companyData['images']['main'] = [
                    "id" => $companyData['images']['main']['id'],
                    "url" => $this->resolvePublicImageUrl(
                        request: $request,
                        params: $companyImageParams,
                        pathResolver: $this->pathResolver,
                        fileName: $companyData['images']['main']['name'],
                        mime: $companyData['images']['main']['mime']
                    )
                ];
            }

                //-- other image
            $companyData['images']['others'] = array_map(
                fn ($data) =>[ 
                    'id' => $data['id'],
                    "url" => $this->resolvePublicImageUrl(
                        request: $request,
                        params: $companyImageParams,
                        pathResolver: $this->pathResolver,
                        fileName: $data['name'],
                        mime: $data['mime']
                    )
                ],
                $companyData['images']['others']
            );

            if($companyData['videoPresentation']){
                $companyData['videoPresentation'] = [
                    "id"  => $companyData['videoPresentation']["id"],
                    "url" =>  $this->resolvePublicImageUrl(
                        request: $request,
                        params: AccountStorageParams::companyVideoPresentation(
                            companyId: $companyId 
                        ),
                        pathResolver: $this->pathResolver,
                        fileName: $companyData['videoPresentation']['name'],
                        mime: $companyData['videoPresentation']['mime']
                    )
                ];
            }

                //-- User
            if ($userData['image'] !== null) {
                $userData['image'] = [
                    "id" =>  $userData['image']['id'],
                    "url" => $this->resolvePublicImageUrl(
                        request: $request,
                        params: AccountStorageParams::recruiterProfile(
                            companyId: $companyId 
                        ),
                        pathResolver: $this->pathResolver,
                        fileName: $userData['image']['name'],
                        mime: $userData['image']['mime']
                    )
                ];
            }


            return ApiResponse::success(
                data: [
                    'company' => $companyData,
                    'user' => $userData,
                ]
            )->toJsonResponse();
        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while retrieving profile information.',
                throwable: $error
            )->toJsonResponse();
        }
    }


        
    /**
     * Get user kpis data
     */
    #[Route("/kpi", methods: ['GET'], name: "view_kpi_metrics")]
    public function getKpi(
        JobOfferAnalyticsRepositoryInterface $jobOfferQueryRepository
    ) {
        try{
            /** @var AuthenticatedPerson */
            $user = $this->getUser();

            $result = $jobOfferQueryRepository->analyseJobOfferCollection(
                userId: $user->getId()
            );

            return ApiResponse::success(
                data: $result,
                message: "Everything went successfully"
            )->toJsonResponse();
        }
        catch(\Exception $exception)
        {
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
        UserRepositoryInterface $userRepository,
        CompanyRepositoryInterface $companyRepository,
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

            //----------------------------------------
            // Retrieving the User Profile
            //----------------------------------------

            $user = $userRepository->findById($authenticatedUser->getId());

            if (!$user) {
                return ApiResponse::error(
                    message: "User not found",
                    statusCode: Response::HTTP_NOT_FOUND
                )->toJsonResponse();
            }

            //--------------------------------------
            // Building the Avatar URL
            //--------------------------------------

            $avatar = null;
            ApiResponse::$logger->error("Company has image: " . json_encode($user->image()));
            if ($user->image()) {
                $avatar = $this->resolvePublicImageUrl(
                    request: $request,
                    pathResolver: $this->pathResolver,
                    params: AccountStorageParams::recruiterProfile(
                        companyId: $user->companyId(),
                        storedFileName: $user->image()->name
                    ),
                    mime: $user->image()->mime,
                );
                ApiResponse::$logger->error("Company user url : " . $avatar);
            }

            $userData = [
                'id'        => $user->id(),
                'firstName' => $user->firstName(),
                'lastName'  => $user->lastName(),
                'email'     => $user->email(),
                'avatarUrl'    => $avatar,
            ];

            //----------------------------------------------
            // Retrieval of company data, if applicable
            //----------------------------------------------

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

                    //---------------------------------
                    //-- Logo URL Structure 
                    //---------------------------------

                    $logoURL = null;
                    if ($company->logo()) {
                        $logoURL = $this->resolvePublicImageUrl(
                            request: $request,
                            params: AccountStorageParams::companyLogo(
                                companyId: $company->id(),
                                storedFileName:  $company->logo()->name
                            ),
                            mime: $company->logo()->mime,
                            pathResolver: $this->pathResolver
                        );
                        ApiResponse::$logger->error("Company logo url : " . $logoURL);
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