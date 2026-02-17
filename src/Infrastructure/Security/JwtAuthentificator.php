<?php

namespace App\Infrastructure\Security;

use DomainException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\ValidAt; 
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtAuthentificator
{
    const JWT_SECRET = "usdyoisdfhqMGHSkkd6@EZFè1+^8Z0.%dk673730ssshvV/5@";

    private Configuration $config;

    public function __construct(){
        $this->config = Configuration::forSymmetricSigner(
                                        new Sha256(),
                                        InMemory::plainText(self::JWT_SECRET),
                                    );
    }

    /**
     * Take in a serializable object and turn
     */
    public function generate(array $playload)
    {

        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
                        ->issuedBy('Hiring_forge')  //iss
                        ->permittedFor('hiring_forge_front') //aud
                        ->issuedAt($now)    //iat
                        ->expiresAt($now->modify("+1 hour")) //exp
                        ->withClaim("playload", $playload)
                        ->getToken(
                            $this->config->signer(),
                            $this->config->signingKey()
                        );
        return $token->toString();
    }

    public function decode(string $jwtTokenString): mixed
    {
        $constraints = [
            new SignedWith(
                $this->config->signer(),
                $this->config->signingKey()
            ),
            new ValidAt(SystemClock::fromUTC())
        ];

        if($this->config->validator()->validate($jwtTokenString, ...$constraints)){
            $token  = $this->config->parser()->parse($jwtTokenString);
            if(!$token  instanceof UnencryptedToken){
                throw new \RuntimeException("Invalid token");
            };

            return $token->claims()->get('playload');
        }

        throw new DomainException("Invalid Token");
    }
}

?>