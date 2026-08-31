<?php


namespace App\Api\Controllers\Notification;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Domain\Notification\RecipientType;


use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/notifications')]
class NotificationQueryController extends AbstractController
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

    /**
     * Route: /notifications/account/read
     * Body:
     *  - notificationIds : array<int,string>
     */
    #[Route('/account/read', methods: ['POST'])]
    public function markAsRead(
        Request $request,
    ): JsonResponse {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'User authentication required.',
                    statusCode: 401
                )->toJsonResponse();
            }

            // Validation
            $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $notificationIds = $body['notificationIds'] ?? null;

            if (!is_array($notificationIds) || empty($notificationIds)) {
                return ApiResponse::error(
                    message: 'Invalid or empty notificationIds array provided.',
                    statusCode: 400
                )->toJsonResponse();
            }

            // Update
            $this->notificationRepository->markNotificationAsRead(
                notificationIds: $notificationIds,
                recipientId: $user->getId()
            );

            return ApiResponse::notice(
                message: 'Notifications marked as read successfully.'
            )->toJsonResponse();

        } catch (\JsonException $error) {
            return ApiResponse::error(
                message: 'Invalid JSON payload.',
                statusCode: 400
            )->toJsonResponse();
        }
        catch (\Throwable $error) {
            return ApiResponse::error(
                message: 'Something went wrong while marking notifications as read.',
                throwable: $error
            )->toJsonResponse();
        }
    }

    
    /**
     * Route: GET /activity/account/recent?limit=7&type=JOB_APPLIED
     */
    #[Route('/account/recent', name: 'api_notifications_account_recent', methods: ['GET'])]
    public function getRecentAccountNotification(Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            //-- limit
            $limitParam = $request->query->get('limit');
            $limit = is_numeric($limitParam) ? (int) $limitParam : 7;

            //-- type
            $typeParam = $request->query->get('type');
            $type = null;

            if ($typeParam !== null) {
                $type = NotificationType::tryFrom($typeParam);
                if ($type === null) {
                    return ApiResponse::error(
                        message: sprintf('Invalid notification type: "%s"', $typeParam),
                        statusCode: 400
                    )->toJsonResponse();
                }
            }

            //-- optimize retrieval
            $data = $this->notificationRepository->getRecentForTarget(
                recipientId: $user->getId(),
                recipientType: RecipientType::ACCOUNT,
                type: $type,
                limit: $limit,
                scheme: [
                    'id' => true,
                    'type' => true,
                    'isRead' => true,
                    'targetUrl' => true,
                    'createdAt' => true,
                    'account' => [
                        'id' => true,
                        'firstName' => true,
                        'lastName' => true,
                    ],
                    'data' => true,
                ]
            );

            return ApiResponse::success(
                data: $data,
                message: 'Recent account notifications retrieved successfully'
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            $this->logger->error('Error fetching recent account notifications', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500,
                throwable: $exception
            )->toJsonResponse();
        }
    }


    /**
     * Route: GET /activity/company/recent?companyId=uuid&limit=7&type=JOB_APPLIED
     */
    #[Route('/company/recent', name: 'api_notifications_company_recent', methods: ['GET'])]
    public function getRecentCompanyNotification(Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();
            $companyId = $request->query->get('companyId');

            if (!$companyId) {
                return ApiResponse::error(
                    message: 'Company context is missing or required.',
                    statusCode: 400
                )->toJsonResponse();
            }

            //-- limit
            $limitParam = $request->query->get('limit');
            $limit = is_numeric($limitParam) ? (int) $limitParam : 7;

            //-- type
            $typeParam = $request->query->get('type');
            $type = null;
            if ($typeParam !== null) {
                $type = NotificationType::tryFrom($typeParam);
                if ($type === null) {
                    return ApiResponse::error(
                        message: sprintf('Invalid notification type: "%s"', $typeParam),
                        statusCode: 400
                    )->toJsonResponse();
                }
            }

            //-- optimize retrieval
            $data = $this->notificationRepository->getRecentForTarget(
                recipientId: $companyId,
                recipientType: RecipientType::COMPANY,
                type: $type,
                limit: $limit,
                scheme: [
                    'id' => true,
                    'type' => true,
                    'isRead' => true,
                    'targetUrl' => true,
                    'createdAt' => true,
                    'account' => [
                        'id' => true,
                        'firstName' => true,
                        'lastName' => true,
                    ],
                    'recipient' => [
                        'id' => true,
                        'name' => true,
                    ],
                    'data' => true,
                ]
            );

            return ApiResponse::success(
                data: $data,
                message: 'Recent company notifications retrieved successfully'
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            $this->logger->error('Error fetching recent company notifications', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500,
                throwable: $exception
            )->toJsonResponse();
        }
    }

    //--------------------------------
    //------- Count unread
    //-------------------------------

    /**
     * Route: GET /activity/account/unread-count
     */
    #[Route('/account/unread-count', name: 'api_notifications_account_unread_count', methods: ['GET'])]
    public function getAccountUnreadCount(): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            $unreadCount = $this->notificationRepository->countUnreadForTarget(
                recipientId: $user->getId(),
                recipientType: RecipientType::ACCOUNT
            );

            return ApiResponse::success(
                data: ['unreadCount' => $unreadCount],
                message: 'Account unread notification count retrieved successfully'
            )->toJsonResponse();

        }
        catch (\Throwable $exception) {
            $this->logger->error('Error counting unread account notifications', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500,
                throwable: $exception
            )->toJsonResponse();
        }
    }


    /**
     * Route: GET /company/unread-count?companyId=uuid
     */
    #[Route('/company/unread-count', name: 'api_notifications_company_unread_count', methods: ['GET'])]
    public function getCompanyUnreadCount(Request $request): JsonResponse
    {
        try {
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();
            $companyId = $request->query->get('companyId');

            if (!$companyId) {
                return ApiResponse::error(
                    message: 'Company context is missing or required.',
                    statusCode: 400
                )->toJsonResponse();
            }

            $unreadCount = $this->notificationRepository->countUnreadForTarget(
                recipientId: $companyId,
                recipientType: RecipientType::COMPANY
            );

            return ApiResponse::success(
                data: ['unreadCount' => $unreadCount],
                message: 'Company unread notification count retrieved successfully'
            )->toJsonResponse();

        }
        catch (\Throwable $exception) {
            $this->logger->error('Error counting unread company notifications', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500,
                throwable: $exception
            )->toJsonResponse();
        }
    }

    /**
     * Route: /user
     * Queries:
     *  - skip: number - offset
     *  - limit: number - max result
     *  - offerId: ?string - id of an offer
     *  - types: string - comma-separated list of types ("JOB_APPLIED,SYSTEM")
     */
    #[Route('/user', methods: ['GET'])]
    public function getNotifications(
        Request $request
    ): JsonResponse {
        $offerId = null;

        try {
            /** @var AuthenticatedPerson|null $user */
            $user = $this->getUser();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthorized action',
                    statusCode: 401
                )->toJsonResponse();
            }

            $skip = max(0, $request->query->getInt('skip', 0));
            $limit = max(1, min($request->query->getInt('limit', 7), 50));
            $offerId = $request->query->get('offerId');

            // Extraction & convert type array
            $typesParam = $request->query->get('types');
            $types = [];

            if (is_string($typesParam) && trim($typesParam) !== '') {
                $rawTypes = array_map('trim', explode(',', $typesParam));

                foreach ($rawTypes as $rawType) {
                    $enumType = NotificationType::tryFrom($rawType);
                    if ($enumType !== null) {
                        $types[] = $enumType;
                    }
                }
            }

            $result = $this->notificationRepository->getNotifications(
                userId: $user->getId(),
                jobId: $offerId,
                limit: $limit,
                skip: $skip,
                types: $types
            );

            return ApiResponse::success(
                data: $result,
                statusCode: 200
            )->toJsonResponse();

        } catch (\Throwable $error) {
            $this->logger->error('Error fetching user notifications', [
                'exception' => $error->getMessage(),
                'offerId'   => $offerId,
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500,
                throwable: $error
            )->toJsonResponse();
        }
    }
}