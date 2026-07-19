<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Security\Tokens;

use App\Domain\Security\TokenBlacklistRepositoryInterface;


use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Override;


final class TokenBlacklistRepository
    implements TokenBlacklistRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    #[Override]
    public function blacklist(string $jti, DateTimeImmutable $expiresAt, string $reason = 'logout'): void
    {
        $entity = new BlacklistedTokenEntity(
            jti: $jti,
            expiresAt: $expiresAt,
            reason: $reason
        );

        $this->em->persist($entity);
        $this->em->flush();
    }

    #[Override]
    public function isBlacklisted(string $jti): bool
    {
        $count = $this->em->getRepository(BlacklistedTokenEntity::class)->count([ "jti" => $jti ]);
    
        return $count > 0;
    }
}