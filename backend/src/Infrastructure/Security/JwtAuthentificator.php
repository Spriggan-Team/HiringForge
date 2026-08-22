<?php

namespace App\Infrastructure\Security;

use DomainException;
use RuntimeException;
use DateTimeImmutable;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;

use Ramsey\Uuid\Uuid;



class JwtAuthentificator
{
    private const JWT_SECRET = "usdyoisdfhqMGHSkkd6@EZFè1+^8Z0.%dk673730ssshvV/5@";

    private Configuration $config;
    public static $JWT_EXPIRATION_DURATION = "+7 hours" ;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(self::JWT_SECRET)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function generate(array $payload): string
    {
        $now = new DateTimeImmutable();
        
        $token = $this->config->builder()
            ->issuedBy('Hiring_forge')
            ->permittedFor('hiring_forge_front')
            ->issuedAt($now)
            ->identifiedBy(Uuid::uuid4()->toString())
            ->expiresAt($now->modify(self::$JWT_EXPIRATION_DURATION))
            ->withClaim("payload", $payload)
            ->getToken(
                $this->config->signer(),
                $this->config->signingKey()
            );

        return $token->toString();
    }



    public function decode(string $jwtTokenString): UnencryptedToken
    {
        //-- Parse the string into a Token object first
        try {
            $token = $this->config->parser()->parse($jwtTokenString);
        }
        catch (\Exception $e) {
            throw new DomainException("Invalid token format structure", 0, $e);
        }

        if (!$token instanceof UnencryptedToken) {
            throw new RuntimeException('Invalid token type instance');
        }

        //--. Define constraints
        $constraints = [
            new SignedWith(
                $this->config->signer(),
                $this->config->signingKey()
            ),
            new LooseValidAt(
                SystemClock::fromUTC()
            )
        ];

        //-- Validate the Token object
        if (!$this->config->validator()->validate($token, ...$constraints)) {
            throw new DomainException("Invalid Token claims or signature mismatch", code: 401);
        }

        return $token;
    }
}