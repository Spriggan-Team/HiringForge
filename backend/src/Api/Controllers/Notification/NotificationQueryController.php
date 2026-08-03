<?php


namespace App\Api\Controllers\Notification;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use Psr\Log\LoggerInterface;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/notification')]
class NotificationQueryController extends AbstractController
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private LoggerInterface $logger
    ){
        ApiResponse::init($logger);
    }

    
    /**
     * Route: /activity?limit=number?type=string
     */
    #[Route('/recent')]
    public function getRecentNotification(Request $request): JsonResponse
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

            //-- optimise retraival
            $data = $this->notificationRepository->getRecent(
                userId: $user->getId(),
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
                message: 'Recent notifications retrieved successfully'
            )->toJsonResponse();

        } catch (\Throwable $exception) {
            $this->logger->error('Error fetching recent notifications', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ApiResponse::error(
                message: 'An unexpected error occurred',
                statusCode: 500
            )->toJsonResponse();
        }
    }
}