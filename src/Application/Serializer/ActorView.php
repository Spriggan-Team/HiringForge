<?php

namespace App\Application\Serializer;

use App\Domain\Auth\TokenPurpose;
use App\Domain\Auth\AuthTokenData;
use App\Domain\Auth\AuthSecurityServiceInterface;
use App\Domain\Auth\RegisterTokenData;
use App\Domain\Shared\Actor\ActorId;
use App\Domain\Shared\Actor\ActorRole;

class ActorView
{
    public function __construct(
        private AuthSecurityServiceInterface $authSecurity
    ) {}

    public function authentificate(string $id, ActorRole $role): array
    {
        $payload = new AuthTokenData(
            $id,
            $role,
            TokenPurpose::AUTHENTIFICATE
        );

        $token = $this->authSecurity->createTokenFor($payload->toArray());
        return ['token' => $token];
    }
    

    public function register(string $id, array $uploadFailed): array
    {
        $payload = new RegisterTokenData($id, TokenPurpose::REGISTER_UPLOAD);
        $token = $this->authSecurity->createTokenFor($payload->toArray());

        return [
            'token' => $token,
            'upload_failed' => $uploadFailed
        ];
    }
}
