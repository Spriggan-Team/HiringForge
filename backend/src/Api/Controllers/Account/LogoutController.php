<?php

namespace App\Api\Controllers\Account;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Security\TokenBlacklistRepositoryInterface;
use App\Infrastructure\Security\JwtAuthentificator;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(TokenBlacklistRepositoryInterface $blacklister): JsonResponse
    {
        try{
            /** @var AuthenticatedPerson $user */
            $user = $this->getUser();

            if ($user) {
                $jti = $user->getJti(); 
                $expiresAt = (new \DateTimeImmutable())->modify(JwtAuthentificator::$JWT_EXPIRATION_DURATION);

                $blacklister->blacklist($jti, $expiresAt, 'user_logout');
            }

            return ApiResponse::notice(message: 'Successfully logged out.', statusCode: 200)->toJsonResponse();
        }
        catch(\Exception $exception){
            return  ApiResponse::notice("Something went wrong")->toJsonResponse();
        }
    }
}